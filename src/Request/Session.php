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
    public $user;

    /**
     * "new" field value.
     *
     * @var null
     */
    public $new;

    /**
     * Session ID.
     *
     * @var null
     */
    public $sessionId;

    /**
     * Attributes array.
     *
     * @var array
     */
    public $attributes = array();

    /**
     * Session constructor.
     *
     * @param array $data Data array.
     */
    public function __construct($data)
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
    public function openSession()
    {
        ini_set('session.use_cookies', 0); # disable session cookies
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
    public function getAttribute($key, $default = false)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        } else {
            return $default;
        }
    }

    /**
     * Removes "SessionId." prefix from the send session id, as it's invalid
     * as a session ID (at least for default session, on file).
     *
     * @param string $sessionId Session ID.
     *
     * @return string
     */
    protected function parseSessionId($sessionId)
    {
        $prefix = 'SessionId.';
        if (substr($sessionId, 0, strlen($prefix)) == $prefix) {
            return substr($sessionId, strlen($prefix));
        } else {
            return $sessionId;
        }
    }

    /**
     * Instantiates a User object for the user data provided with the request.
     *
     * @param array $data Data array.
     */
    protected function fetchUser($data)
    {
        $this->user = isset($data['user']) ? new User($data['user']) : null;
    }

    /**
     * Fetches the session ID provided with the request.
     *
     * @param array $data Data array.
     */
    protected function fetchSessionId($data)
    {
        $this->sessionId = isset($data['sessionId']) ? $data['sessionId'] : null;
    }

    /**
     * Fetches the "new" field provided with the request.
     *
     * @param array $data Data array.
     */
    protected function fetchNew($data)
    {
        $this->new = isset($data['new']) ? $data['new'] : null;
    }

    /**
     * Fetches the attributes provided with the request.
     *
     * @param array $data Data array.
     */
    protected function fetchAttributes($data)
    {
        if (!$this->new && isset($data['attributes'])) {
            $this->attributes = $data['attributes'];
        }
    }
}
