<?php

namespace randomhost\Alexa\Request;

use DateTime;
use Exception;
use InvalidArgumentException;
use RuntimeException;

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
    private const TIMESTAMP_VALID_TOLERANCE_SECONDS = 30;

    /**
     * Protocol to validate against.
     */
    private const SIGNATURE_VALID_PROTOCOL = 'https';

    /**
     * Hostname to validate against.
     */
    private const SIGNATURE_VALID_HOSTNAME = 's3.amazonaws.com';

    /**
     * Path to validate against.
     */
    private const SIGNATURE_VALID_PATH = '/echo.api/';

    /**
     * Port to validate against.
     */
    private const SIGNATURE_VALID_PORT = 443;

    /**
     * Service domain to validate against.
     */
    private const ECHO_SERVICE_DOMAIN = 'echo-api.amazon.com';

    /**
     * Encryption method.
     */
    private const ENCRYPT_METHOD = 'sha1WithRSAEncryption';

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
    public function __construct(string $certificateUrl, string $signature)
    {
        $this->certificateUrl = $certificateUrl;
        $this->requestSignature = $signature;
    }

    /**
     * Validates the given request data.
     *
     * @param string $requestData JSON encoded string.
     *
     * @throws InvalidArgumentException if the certificate is invalid.
     * @throws Exception                if DateTime object instantiation fails.
     */
    public function validateRequest(string $requestData): void
    {
        $requestParsed = json_decode($requestData, true);

        $this
            ->validateDataFormat($requestParsed)
            ->validateTimestamp($requestParsed['request']['timestamp'])
            ->verifySignatureCertificateURL()
            ->validateCertificate()
            ->validateRequestSignature($requestData)
        ;
    }

    /**
     * Validates the given request data format.
     *
     * @param mixed $requestParsed Request data as returned by json_decode().
     *
     * @throws InvalidArgumentException if the request dost not contain a timestamp.
     *
     * @return $this
     */
    protected function validateDataFormat($requestParsed): self
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
     * @throws Exception                if DateTime object instantiation fails.
     * @throws InvalidArgumentException if the the timestamp is too old.
     *
     * @return $this
     */
    protected function validateTimestamp(string $timestamp): self
    {
        $now = new DateTime();
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
     * @throws InvalidArgumentException if the remote certificate is not found.
     *
     * @return $this
     */
    protected function validateCertificate(): self
    {
        $this->certificateContent = $this->getCertificate();
        $parsedCertificate = $this->parseCertificate($this->certificateContent);

        if (null == $parsedCertificate) {
            throw new InvalidArgumentException(
                'Remote certificate was not found'
            );
        }

        $this
            ->validateCertificateDate($parsedCertificate)
            ->validateCertificateSAN($parsedCertificate, static::ECHO_SERVICE_DOMAIN)
        ;

        return $this;
    }

    /**
     * Validates the request signature.
     *
     * @param string $requestData Request data string.
     *
     * @throws InvalidArgumentException if the request signature could not be verified.
     *
     * @return $this
     */
    protected function validateRequestSignature(string $requestData): self
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
     * @throws InvalidArgumentException if the remote certificate has expired.
     *
     * @return $this
     */
    protected function validateCertificateDate(array $parsedCertificate): self
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
     * @throws InvalidArgumentException if the remote certificate does not contain a valid SAN.
     *
     * @return $this
     */
    protected function validateCertificateSAN(array $parsedCertificate, string $amazonServiceDomain): self
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
     * @throws InvalidArgumentException if the URL of the certificate contains invalid data.
     *
     * @return $this
     */
    protected function verifySignatureCertificateURL(): self
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
        }
        if ($url['host'] !== static::SIGNATURE_VALID_HOSTNAME) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid host name. Expected %s, got %s',
                    var_export(static::SIGNATURE_VALID_HOSTNAME, true),
                    var_export($url['host'], true)
                )
            );
        }
        if (0 !== strpos($url['path'], static::SIGNATURE_VALID_PATH)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid path. Must start with %s, got %s',
                    var_export(static::SIGNATURE_VALID_PATH, true),
                    var_export($url['path'], true)
                )
            );
        }
        if (isset($url['port']) && $url['port'] !== static::SIGNATURE_VALID_PORT) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid port. Expected %s, got %s',
                    var_export(static::SIGNATURE_VALID_PORT, true),
                    var_export($url['port'], true)
                )
            );
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
            throw new RuntimeException(
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
