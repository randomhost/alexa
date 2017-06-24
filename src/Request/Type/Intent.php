<?php

namespace randomhost\Alexa\Request\Type;

use randomhost\Alexa\Request\Request;

/**
 * Represents an Intent request.
 */
class Intent extends Request
{
    /**
     * Intent name.
     *
     * @var string
     */
    protected $intentName;

    /**
     * Slots.
     *
     * @var array
     */
    protected $slots = array();

    /**
     * Constructor.
     *
     * @param string $rawData Raw request data.
     */
    public function __construct($rawData)
    {
        parent::__construct($rawData);

        $this
            ->fetchIntentName()
            ->fetchSlots();
    }

    /**
     * Returns the Intent name.
     *
     * @return string
     */
    public function getIntentName()
    {
        return $this->intentName;
    }

    /**
     * Returns the value for the requested intent slot, or $default if not found.
     *
     * @param string $name    Slot name.
     * @param mixed  $default Fallback value to return.
     *
     * @return mixed
     */
    public function getSlot($name, $default = false)
    {
        if (array_key_exists($name, $this->slots)) {
            return $this->slots[$name];
        } else {
            return $default;
        }
    }

    /**
     * Fetches the name provided with the intent request.
     *
     * @return $this
     */
    protected function fetchIntentName()
    {
        if (isset($this->data['request']['intent']['name'])) {
            $this->intentName = $this->data['request']['intent']['name'];
        }

        return $this;
    }

    /**
     * Fetches the slots provided with the intent request.
     *
     * @return $this
     */
    protected function fetchSlots()
    {
        if (isset($this->data['request']['intent']['slots'])) {
            foreach ($this->data['request']['intent']['slots'] as $slot) {
                if (isset($slot['value'])) {
                    $this->slots[$slot['name']] = $slot['value'];
                }
            }
        }

        return $this;
    }
}
