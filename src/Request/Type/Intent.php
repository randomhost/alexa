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
    protected $slots = [];

    /**
     * Constructor.
     *
     * @param array $data JSON data array.
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        $this
            ->fetchIntentName()
            ->fetchSlots()
        ;
    }

    /**
     * Returns the Intent name.
     *
     * @return string
     */
    public function getIntentName(): string
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
    public function getSlot(string $name, $default = false)
    {
        if (array_key_exists($name, $this->slots)) {
            return $this->slots[$name];
        }

        return $default;
    }

    /**
     * Fetches the name provided with the intent request.
     *
     * @return $this
     */
    protected function fetchIntentName(): self
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
    protected function fetchSlots(): self
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
