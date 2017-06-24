<?php

namespace randomhost\Alexa\Request;

use DateTime;
use InvalidArgumentException;

/**
 * Validates the request signature.
 *
 * Based on code from alexa-app: https://github.com/develpr/alexa-app by Kevin Mitchell
 */
class Certificate
{
    /**
     * Maximum acceptable difference between timestamps.
     */
    const TIMESTAMP_VALID_TOLERANCE_SECONDS = 30;

    /**
     * Protocol to validate against.
     */
    const SIGNATURE_VALID_PROTOCOL = 'https';

    /**
     * Hostname to validate against.
     */
    const SIGNATURE_VALID_HOSTNAME = 's3.amazonaws.com';

    /**
     * Path to validate against.
     */
    const SIGNATURE_VALID_PATH = '/echo.api/';

    /**
     * Port to validate against.
     */
    const SIGNATURE_VALID_PORT = 443;

    /**
     * Service domain to validate against.
     */
    const ECHO_SERVICE_DOMAIN = 'echo-api.amazon.com';

    /**
     * Encryption method.
     */
    const ENCRYPT_METHOD = "sha1WithRSAEncryption";

    /**
     * Certificate URL.
     *
     * @var string
     */
    protected $certificateUrl;

    /**
     * Certificate content.
     *
     * @var mixed
     */
    protected $certificateContent;

    /**
     * Request signature.
     *
     * @var string
     */
    protected $requestSignature;

    /**
     * Certificate constructor.
     *
     * @param string $certificateUrl Certificate URL.
     * @param string $signature      Certificate signature.
     */
    public function __construct($certificateUrl, $signature)
    {
        $this->certificateUrl = $certificateUrl;
        $this->requestSignature = $signature;
    }

    /**
     * Validates the given request data.
     *
     * @param string $requestData JSON encoded string.
     *
     * @throws InvalidArgumentException
     */
    public function validateRequest($requestData)
    {
        $requestParsed = json_decode($requestData, true);

        try {
            $this
                ->validateDataFormat($requestParsed)
                ->validateTimestamp($requestParsed['request']['timestamp'])
                ->verifySignatureCertificateURL()
                ->validateCertificate()
                ->validateRequestSignature($requestData);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            throw $e;
        }

        http_response_code(200);
    }

    /**
     * Validates the given request data format.
     *
     * @param mixed $requestParsed Request data as returned by json_decode().
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    protected function validateDataFormat($requestParsed)
    {
        if (!is_array($requestParsed)) {
            throw new InvalidArgumentException(
                'Data could not be decoded'
            );
        }

        if (!isset($requestParsed['request']['timestamp'])) {
            throw new InvalidArgumentException(
                'Data array does not contain a timestamp'
            );
        }

        return $this;
    }

    /**
     * Validates the request timestamp.
     *
     * @param string $timestamp Timestamp.
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    protected function validateTimestamp($timestamp)
    {
        $now = new DateTime;
        $timestamp = new DateTime($timestamp);
        $differenceInSeconds = $now->getTimestamp() - $timestamp->getTimestamp();

        if ($differenceInSeconds > self::TIMESTAMP_VALID_TOLERANCE_SECONDS) {
            throw new InvalidArgumentException(
                'Request timestamp was too old. Possible replay attack.'
            );
        }

        return $this;
    }

    /**
     * Validates the certificate.
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    protected function validateCertificate()
    {
        $this->certificateContent = $this->getCertificate();
        $parsedCertificate = $this->parseCertificate($this->certificateContent);

        if ($parsedCertificate == null) {
            throw new InvalidArgumentException(
                'Remote certificate was not found'
            );
        }

        $this
            ->validateCertificateDate($parsedCertificate)
            ->validateCertificateSAN($parsedCertificate, static::ECHO_SERVICE_DOMAIN);

        return $this;
    }

    /**
     * Validates the request signature.
     *
     * @param string $requestData Request data string.
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    protected function validateRequestSignature($requestData)
    {
        $certKey = openssl_pkey_get_public($this->certificateContent);

        $valid = openssl_verify(
            $requestData,
            base64_decode($this->requestSignature),
            $certKey,
            self::ENCRYPT_METHOD
        );
        if (!$valid) {
            throw new InvalidArgumentException('Request signature could not be verified');
        }

        return $this;
    }

    /**
     * Validates the certificate date.
     *
     * @param array $parsedCertificate Parsed certificate data.
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    protected function validateCertificateDate(array $parsedCertificate)
    {
        $validFrom = $parsedCertificate['validFrom_time_t'];
        $validTo = $parsedCertificate['validTo_time_t'];
        $time = time();

        if ($validFrom > $time || $validTo < $time) {
            throw new InvalidArgumentException(
                'Remote certificate has expired'
            );
        }

        return $this;
    }

    /**
     * Validates the service domain.
     *
     * @param array  $parsedCertificate   Parsed certificate data.
     * @param string $amazonServiceDomain Amazon service domain.
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    protected function validateCertificateSAN(array $parsedCertificate, $amazonServiceDomain)
    {
        if (!isset($parsedCertificate['extensions']['subjectAltName'])
            || false === strpos(
                $parsedCertificate['extensions']['subjectAltName'],
                $amazonServiceDomain
            )
        ) {
            throw new InvalidArgumentException(
                'Remote certificate does not contain a valid SAN in the signature'
            );
        }

        return $this;
    }

    /**
     * Verifies the URL of the certificate.
     *
     * @author Emanuele Corradini <emanuele@evensi.com>
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    protected function verifySignatureCertificateURL()
    {
        $url = parse_url($this->certificateUrl);

        if ($url['scheme'] !== static::SIGNATURE_VALID_PROTOCOL) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid protocol. Expected %s, got %s',
                    var_export(static::SIGNATURE_VALID_PROTOCOL, true),
                    var_export($url['scheme'], true)
                )
            );
        } else {
            if ($url['host'] !== static::SIGNATURE_VALID_HOSTNAME) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Invalid host name. Expected %s, got %s',
                        var_export(static::SIGNATURE_VALID_HOSTNAME, true),
                        var_export($url['host'], true)
                    )
                );
            } else {
                if (strpos($url['path'], static::SIGNATURE_VALID_PATH) !== 0) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Invalid path. Must start with %s, got %s',
                            var_export(static::SIGNATURE_VALID_PATH, true),
                            var_export($url['path'], true)
                        )
                    );
                } else {
                    if (isset($url['port']) && $url['port'] !== static::SIGNATURE_VALID_PORT) {
                        throw new InvalidArgumentException(
                            sprintf(
                                'Invalid port. Expected %s, got %s',
                                var_export(static::SIGNATURE_VALID_PORT, true),
                                var_export($url['port'], true)
                            )
                        );
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Parses the X509 certificate.
     *
     * @param mixed $certificate The certificate contents.
     *
     * @return array
     */
    protected function parseCertificate($certificate)
    {
        return openssl_x509_parse($certificate);
    }

    /**
     * Returns the certificate to the underlying code by fetching it from it's location.
     *
     * Override this function if you wish to cache the certificate for a specific time.
     *
     * @return mixed
     */
    protected function getCertificate()
    {
        return $this->fetchCertificate();
    }

    /**
     * Downloads the certificate and returns the content.
     *
     * @return mixed
     */
    protected function fetchCertificate()
    {
        if (!function_exists('curl_init')) {
            throw new InvalidArgumentException(
                'CURL is required to download the signature certificate.'
            );
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->certificateUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $st = curl_exec($ch);
        curl_close($ch);

        return $st;
    }
}
