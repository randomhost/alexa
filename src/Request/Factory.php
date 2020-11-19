<?php

namespace randomhost\Alexa\Request;

use DateTime;
use InvalidArgumentException;

/**
 * Request factory.
 */
class Factory
{
    /**
     * Returns the correct type of Request class.
     *
     * @param string $rawData       JSON encoded data.
     * @param string $applicationId Application ID.
     *
     * @throws InvalidArgumentException
     *
     * @return Request Appropriate Request class for the request type.
     */
    public function getInstanceForData(string $rawData, string $applicationId): Request
    {
        // parse JSON data
        $parser = $this->buildDataParser();
        $data = $parser->parseRawData($rawData);

        // fetch required parameters
        $requestId = $parser->fetchRequestId($data);
        $requestType = $parser->fetchRequestType($data);
        $requestApplicationId = $parser->fetchApplicationId($data);
        $timeStamp = $parser->fetchTimestamp($data);
        $sessionData = $parser->fetchSession($data);

        // validate application ID
        $this->buildApplication($applicationId)
            ->setRequestApplicationId($requestApplicationId)
            ->validateApplicationId()
        ;

        // validate certificate
        $this->buildCertificate()
            ->validateRequest($rawData)
        ;

        // build Session
        $session = $this->buildSession($sessionData);

        // build DateTime
        $dateTime = $this->buildDateTime($timeStamp);

        // build Request
        $request = $this->buildRequest($requestType, $data);
        $request
            ->setRequestId($requestId)
            ->setTimestamp($dateTime)
            ->setSession($session)
        ;

        return $request;
    }

    /**
     * Returns a new DataParser instance.
     *
     * @return DataParser DataParser instance.
     */
    private function buildDataParser(): DataParser
    {
        return new DataParser();
    }

    /**
     * Returns a new Application instance.
     *
     * @param string $requestApplicationId Application ID from request data.
     *
     * @return Application Application instance.
     */
    private function buildApplication(string $requestApplicationId): Application
    {
        return new Application($requestApplicationId);
    }

    /**
     * Returns a new Certificate instance.
     *
     * @return Certificate Certificate instance.
     */
    private function buildCertificate(): Certificate
    {
        return new Certificate(
            $_SERVER['HTTP_SIGNATURECERTCHAINURL'],
            $_SERVER['HTTP_SIGNATURE']
        );
    }

    /**
     * Returns a new Session instance.
     *
     * @param array $sessionData Session data.
     *
     * @return Session Session instance, filled with given session data.
     */
    private function buildSession(array $sessionData): Session
    {
        return new Session($sessionData);
    }

    /**
     * Returns a new DateTime instance.
     *
     * @param string $timeStamp Timestamp.
     *
     * @throws \Exception if DateTime object instantiation fails.
     *
     * @return DateTime DateTime instance for the given unix timestamp.
     */
    private function buildDateTime(string $timeStamp): DateTime
    {
        return new DateTime($timeStamp);
    }

    /**
     * Returns a Request implementation for the given request type.
     *
     * @param string $requestType Request type.
     * @param array  $data        JSON data array.
     *
     * @return Request Appropriate Request implementation for the given parameters.
     */
    private function buildRequest(string $requestType, array $data): Request
    {
        $className = $this->buildRequestClassName($requestType);

        return new $className($data);
    }

    /**
     * Returns the name of the request class.
     *
     * @param string $requestType Raw request type.
     *
     * @return string Full class path of an appropriate Request implementation.
     */
    private function buildRequestClassName(string $requestType): string
    {
        $suffixPos = strrpos($requestType, 'Request');
        if (false === $suffixPos) {
            throw new InvalidArgumentException(
                sprintf(
                    'Malformed request type %s',
                    var_export($requestType, true)
                )
            );
        }

        $className = __NAMESPACE__.'\\Type\\'.substr($requestType, 0, $suffixPos);

        if (!class_exists($className)) {
            throw new InvalidArgumentException('Unknown request type: '.$className);
        }

        return $className;
    }
}
