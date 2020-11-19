<?php

namespace randomhost\Alexa\Response;

/**
 * Represents a LinkAccount response.
 *
 * @todo Figure out how this works.
 */
class LinkAccount
{
    /**
     * @return array
     */
    public function render()
    {
        return [
            'type' => 'LinkAccount',
        ];
    }
}
