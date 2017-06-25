<?php

namespace randomhost\Alexa\Request;

use DateTime;

/**
 * Represents a request sent by the Alexa platform.
 */
abstract class Request
{
    /**
     * Request ID.
     *
     * @var string
     */
    protected $requestId;

    /**
     * Timestamp.
     *
     * @var DateTime
     */
    protected $timestamp;

    /**
     * Session instance.
     *
     * @var Session
     */
    protected $session;

    /**
     * JSON data.
     *
     * @var mixed[]
     */
    protected $data;

    /**
     * Constructor.
     *
     * @param array $data JSON data array.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @param string $requestId Request ID.
     *
     * @return $this
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;

        return $this;
    }

    /**
     * @param DateTime $timestamp DateTime instance.
     *
     * @return $this
     */
    public function setTimestamp(DateTime $timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * @param Session $session Session instance.
     *
     * @return $this
     */
    public function setSession(Session $session)
    {
        $this->session = $session;

        return $this;
    }
}
