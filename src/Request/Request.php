<?php

namespace randomhost\Alexa\Request;

use DateTime;
use InvalidArgumentException;
use RuntimeException;

/**
 * Represents a request sent by the Alexa platform.
 */
class Request
{
    /**
     * Request ID.
     *
     * @var string
     */
    public $requestId;

    /**
     * Timestamp.
     *
     * @var string
     */
    public $timestamp;

    /**
     * Session instance.
     *
     * @var Session
     */
    public $session;

    /**
     * Decoded JSON data.
     *
     * @var mixed[]
     */
    public $data;

    /**
     * Raw JSON data.
     *
     * @var string
     */
    public $rawData;

    /**
     * Application ID.
     *
     * @var string
     */
    public $applicationId;

    /**
     * Certificate instance.
     *
     * @var Certificate
     */
    private $certificate;

    /**
     * Application instance.
     *
     * @var Application
     */
    private $application;

    /**
     * Constructor.
     *
     * @param string      $rawData       Raw JSON data.
     * @param null|string $applicationId Application ID.
     */
    public function __construct($rawData, $applicationId = null)
    {
        if (!is_string($rawData)) {
            throw new InvalidArgumentException(
                'Alexa request requires the raw JSON data to validate request signature'
            );
        }

        $this->rawData = $rawData;

        $this->parseRawData();

        $this->fetchRequestId();
        $this->fetchTimestamp();
        $this->fetchSession();
        $this->fetchApplicationId($applicationId);
    }

    /**
     * Accept the certificate validator dependency in order to allow people
     * to extend it to for example cache their certificates.
     *
     * @param Certificate $certificate
     */
    public function setCertificateDependency(Certificate $certificate)
    {
        $this->certificate = $certificate;
    }

    /**
     * Accept the application validator dependency in order to allow people
     * to extend it.
     *
     * @param Application $application
     */
    public function setApplicationDependency(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Instantiates the correct type of Request class, based on the $json->request->type value.
     *
     * @return Request Appropriate Request class for the request type.
     *
     * @throws RuntimeException
     */
    public function fromData()
    {
        // Instantiate a new Certificate validator if none is injected as our dependency.
        if (!isset($this->certificate)) {
            $this->certificate = new Certificate(
                $_SERVER['HTTP_SIGNATURECERTCHAINURL'],
                $_SERVER['HTTP_SIGNATURE']
            );
        }
        if (!isset($this->application)) {
            $this->application = new Application($this->applicationId);
        }

        // We need to ensure that the request Application ID matches our Application ID.
        $this->application->setRequestApplicationId(
            $this->data['session']['application']['applicationId']
        );
        $this->application->validateApplicationId();

        // Validate that the request signature matches the certificate.
        $this->certificate->validateRequest($this->rawData);

        $requestType = $this->data['request']['type'];
        if (!class_exists(__NAMESPACE__.'\\'.$requestType)) {
            throw new RuntimeException('Unknown request type: '.$requestType);
        }

        $className = __NAMESPACE__.'\\'.$requestType;

        $request = new $className($this->rawData, $this->applicationId);

        return $request;
    }

    /**
     * Parses raw json data.
     *
     * @return $this
     */
    protected function parseRawData()
    {
        $data = json_decode($this->rawData, true);
        if (is_null($data)) {
            throw new RuntimeException(
                'Could not decode JSON data'
            );
        }

        $this->data = $data;

        return $this;
    }

    /**
     * Fetches the request ID provided with the request.
     *
     * @return $this
     *
     * @throws RuntimeException
     */
    protected function fetchRequestId()
    {
        if (!isset($this->data['request']['requestId'])) {
            throw new RuntimeException(
                'Request does not contain required field "requestId"'
            );
        }

        $this->requestId = $this->data['request']['requestId'];

        return $this;
    }

    /**
     * Fetches the timestamp provided with the request.
     *
     * @return $this
     *
     * @throws RuntimeException
     */
    protected function fetchTimestamp()
    {
        if (!isset($this->data['request']['timestamp'])) {
            throw new RuntimeException(
                'Request does not contain required field "timestamp"'
            );
        }

        $this->timestamp = new DateTime($this->data['request']['timestamp']);

        return $this;
    }

    /**
     * Fetches the session provided with the request.
     *
     * @return $this
     *
     * @throws RuntimeException
     */
    protected function fetchSession()
    {
        if (!isset($this->data['session'])) {
            throw new RuntimeException(
                'Request does not contain required field "session"'
            );
        }

        $this->session = new Session($this->data['session']);

        return $this;
    }

    /**
     * Fetches the application ID provided with the request.
     *
     * @param null|string $applicationId Fallback application ID.
     *
     * @return $this
     *
     * @throws RuntimeException
     */
    protected function fetchApplicationId($applicationId)
    {
        if (is_null($applicationId)
            && isset($this->data['session']['application']['applicationId'])
        ) {
            $this->applicationId = $this->data['session']['application']['applicationId'];
        } else {
            $this->applicationId = $applicationId;
        }

        return $this;
    }

}
