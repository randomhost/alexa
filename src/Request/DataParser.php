<?php

namespace randomhost\Alexa\Request;

use InvalidArgumentException;

/**
 * JSON data parser.
 */
class DataParser
{
    /**
     * Parses the given JSON data and returns an array.
     *
     * @param string $rawData JSON string.
     *
     * @return array Decoded JSON string.
     */
    public function parseRawData(string $rawData): array
    {
        if (!is_string($rawData)) {
            throw new InvalidArgumentException(
                'Given data is not a valid JSON string'
            );
        }

        $data = json_decode($rawData, true);
        if (is_null($data)) {
            throw new InvalidArgumentException(
                'Could not decode JSON data'
            );
        }

        return $data;
    }

    /**
     * Returns the request ID provided with the request.
     *
     * @param array $data JSON data.
     *
     * @throws InvalidArgumentException
     *
     * @return string Request ID.
     */
    public function fetchRequestId(array $data): string
    {
        if (!isset($data['request']['requestId'])) {
            throw new InvalidArgumentException(
                'Request does not contain field "requestId"'
            );
        }

        return $data['request']['requestId'];
    }

    /**
     * Returns the request type provided with the request.
     *
     * @param array $data JSON data.
     *
     * @throws InvalidArgumentException
     *
     * @return string Request type.
     */
    public function fetchRequestType(array $data): string
    {
        if (!isset($data['request']['type'])) {
            throw new InvalidArgumentException(
                'Request does not contain field "type"'
            );
        }

        return $data['request']['type'];
    }

    /**
     * Returns the timestamp provided with the request.
     *
     * @param array $data JSON data.
     *
     * @throws InvalidArgumentException
     *
     * @return string Request timestamp.
     */
    public function fetchTimestamp(array $data): string
    {
        if (!isset($data['request']['timestamp'])) {
            throw new InvalidArgumentException(
                'Request does not contain field "timestamp"'
            );
        }

        return $data['request']['timestamp'];
    }

    /**
     * Returns the session data provided with the request.
     *
     * @param array $data JSON data.
     *
     * @throws InvalidArgumentException
     *
     * @return array
     */
    public function fetchSession(array $data): array
    {
        if (!isset($data['session'])) {
            throw new InvalidArgumentException(
                'Request does not contain field "session"'
            );
        }

        return $data['session'];
    }

    /**
     * Returns the application ID provided with the request.
     *
     * @param array $data JSON data.
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public function fetchApplicationId(array $data): string
    {
        if (!isset($data['session']['application']['applicationId'])) {
            throw new InvalidArgumentException(
                'Request does not contain field "applicationId"'
            );
        }

        return $data['session']['application']['applicationId'];
    }
}
