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
     * @return Request Appropriate Request class for the request type.
     *
     * @throws InvalidArgumentException
     */
    public function getInstanceForData($rawData, $applicationId)
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
            ->validateApplicationId();

        // validate certificate
        $this->buildCertificate()
            ->validateRequest($rawData);

        // build Session
        $session = $this->buildSession($sessionData);

        // build DateTime
        $dateTime = $this->buildDateTime($timeStamp);

        // build Request
        $request = $this->buildRequest($requestType, $data);
        $request
            ->setRequestId($requestId)
            ->setTimestamp($dateTime)
            ->setSession($session);

        return $request;
    }

    /**
     * Returns a new DataParser instance.
     *
     * @return DataParser
     */
    private function buildDataParser()
    {
        return new DataParser();
    }

    /**
     * Returns a new Application instance.
     *
     * @param string $requestApplicationId Application ID from request data.
     *
     * @return Application
     */
    private function buildApplication($requestApplicationId)
    {
        return new Application($requestApplicationId);
    }

    /**
     * Returns a new Certificate instance.
     *
     * @return Certificate
     */
    private function buildCertificate()
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
     * @return Session
     */
    private function buildSession(array $sessionData)
    {
        $session = new Session($sessionData);

        return $session;
    }

    /**
     * Returns a new DateTime instance.
     *
     * @param string $timeStamp Timestamp.
     *
     * @return DateTime
     */
    private function buildDateTime($timeStamp)
    {
        return new DateTime($timeStamp);
    }

    /**
     * Returns a Request implementation for the given request type.
     *
     * @param string $requestType Request type.
     * @param array  $data        JSON data array.
     *
     * @return Request
     */
    private function buildRequest($requestType, array $data)
    {
        $className = $this->buildRequestClassName($requestType);

        return new $className($data);
    }

    /**
     * Returns the name of the request class.
     *
     * @param string $requestType Raw request type.
     *
     * @return string
     */
    private function buildRequestClassName($requestType)
    {
        $suffixPos = strrpos($requestType, 'Request');
        if ($suffixPos === false) {
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
