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
    protected $userId;

    /**
     * Access token.
     *
     * @var string
     */
    protected $accessToken;

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
    protected function fetchUserId(array $data): void
    {
        $this->userId = $data['userId'] ?? null;
    }

    /**
     * Fetches the access token provided with the request.
     *
     * @param array $data Data array.
     */
    protected function fetchAccessToken($data): void
    {
        $this->accessToken = $data['accessToken'] ?? null;
    }
}
