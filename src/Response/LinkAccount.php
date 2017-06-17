<?php

namespace randomhost\Alexa\Response;

/**
 * Represents a LinkAccount response.
 */
class LinkAccount
{
    public $type = 'LinkAccount';

    public function render()
    {
        return array(
            'type' => $this->type,
        );
    }
}
