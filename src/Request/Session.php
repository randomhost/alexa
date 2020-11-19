<?php

namespace randomhost\Alexa\Request;

/**
 * Represents a session.
 */
class Session
{
    /**
     * User instance.
     *
     * @var User
     */
    protected $user;

    /**
     * "new" field value.
     *
     * @var null
     */
    protected $new;

    /**
     * Session ID.
     *
     * @var null
     */
    protected $sessionId;

    /**
     * Attributes array.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Session constructor.
     *
     * @param array $data Data array.
     */
    public function __construct(array $data)
    {
        $this->fetchUser($data);
        $this->fetchSessionId($data);
        $this->fetchNew($data);
        $this->fetchAttributes($data);
    }

    /**
     * Opens a PHP SESSION using amazon provided sessionId, for storing data about the session.
     * Session cookie won't be sent.
     */
    public function openSession(): bool
    {
        ini_set('session.use_cookies', 0); // disable session cookies
        session_id($this->parseSessionId($this->sessionId));

        return session_start();
    }

    /**
     * Returns the attribute value for the given key or $default if it is not set.
     *
     * @param string $key     Attribute key.
     * @param mixed  $default Fallback value.
     *
     * @return mixed
     */
    public function getAttribute(string $key, $default = false)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return $default;
    }

    /**
     * Removes "SessionId." prefix from the send session id, as it's invalid
     * as a session ID (at least for default session, on file).
     *
     * @param string $sessionId Session ID.
     *
     * @return string Session ID.
     */
    protected function parseSessionId(string $sessionId): string
    {
        $prefix = 'SessionId.';
        if (substr($sessionId, 0, strlen($prefix)) == $prefix) {
            return substr($sessionId, strlen($prefix));
        }

        return $sessionId;
    }

    /**
     * Instantiates a User object for the user data provided with the request.
     *
     * @param array $data Data array.
     */
    protected function fetchUser($data): void
    {
        $this->user = isset($data['user']) ? new User($data['user']) : null;
    }

    /**
     * Fetches the session ID provided with the request.
     *
     * @param array $data Data array.
     */
    protected function fetchSessionId($data): void
    {
        $this->sessionId = $data['sessionId'] ?? null;
    }

    /**
     * Fetches the "new" field provided with the request.
     *
     * @param array $data Data array.
     */
    protected function fetchNew($data): void
    {
        $this->new = $data['new'] ?? null;
    }

    /**
     * Fetches the attributes provided with the request.
     *
     * @param array $data Data array.
     */
    protected function fetchAttributes($data): void
    {
        if (!$this->new && isset($data['attributes'])) {
            $this->attributes = $data['attributes'];
        }
    }
}
