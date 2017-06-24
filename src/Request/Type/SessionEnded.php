<?php

namespace randomhost\Alexa\Request;

/**
 * Represents a SessionEnded request
 *
 * @package Alexa\Request
 */
class SessionEndedRequest extends Request
{
    /**
     * Reason why the session was ended.
     *
     * @var string
     */
    protected $reason = '';

    /**
     * Constructor.
     *
     * @param string $rawData Raw request data.
     */
    public function __construct($rawData)
    {
        parent::__construct($rawData);

        $this->fetchReason();
    }

    /**
     * Returns the reason why the session was ended..
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Fetches the reason provided with the session ended request.
     *
     * @return $this
     */
    protected function fetchReason()
    {
        if (isset($this->data['request']['reason'])) {
            $this->reason = $this->data['request']['reason'];
        }

        return $this;
    }
}
