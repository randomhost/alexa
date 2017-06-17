<?php

namespace randomhost\Alexa\Request;

/**
 * Represents a user.
 */
class User
{
    /**
     * User ID.
     *
     * @var string
     */
    public $userId;

    /**
     * Access token.
     *
     * @var string
     */
    public $accessToken;

    /**
     * Constructor.
     *
     * @param array $data User data.
     */
    public function __construct($data)
    {
        $this->fetchUserId($data);
        $this->fetchAccessToken($data);
    }

    /**
     * Fetches the user ID provided with the request.
     *
     * @param array $data Data array.
     */
    protected function fetchUserId($data)
    {
        $this->userId = isset($data['userId']) ? $data['userId'] : null;
    }

    /**
     * Fetches the access token provided with the request.
     *
     * @param array $data Data array.
     */
    protected function fetchAccessToken($data)
    {
        $this->accessToken = isset($data['accessToken']) ? $data['accessToken'] : null;
    }
}
