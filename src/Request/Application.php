<?php

namespace randomhost\Alexa\Request;

use InvalidArgumentException;

/**
 * Application abstraction layer providing Application ID validation to Alexa requests.
 *
 * Any implementations might provide their own implementations via the
 * $request->setApplicationAbstraction() function but must provide the
 * validateApplicationId() function.
 */
class Application
{
    /**
     * Array of application IDs.
     *
     * @var string[]
     */
    public $applicationId;

    /**
     * Application ID provided with the request.
     *
     * @var string
     */
    public $requestApplicationId;

    /**
     * Constructor.
     *
     * @param string $applicationId Comma separated list of application IDs.
     */
    public function __construct($applicationId)
    {
        $this->applicationId = explode(',', $applicationId);
    }

    /**
     * Sets the application ID provided with the request.
     *
     * @param string $applicationId Application ID provided with the request.
     *
     * @return $this
     */
    public function setRequestApplicationId($applicationId)
    {
        $this->requestApplicationId = $applicationId;

        return $this;
    }

    /**
     * Validates that the request application ID matches the configured application ID.
     *
     * This is required as per Amazon requirements.
     *
     * @return $this
     */
    public function validateApplicationId()
    {
        if (!in_array($this->requestApplicationId, $this->applicationId)) {
            throw new InvalidArgumentException('Application ID does not match');
        }

        return $this;
    }
}
