<?php

namespace randomhost\Alexa\Request\Type;

use randomhost\Alexa\Request\Request;

/**
 * Represents a SessionEnded request
 *
 * @package Alexa\Request
 */
class SessionEnded extends Request
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
     * @param array $data JSON data array.
     */
    public function __construct($data)
    {
        parent::__construct($data);

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
