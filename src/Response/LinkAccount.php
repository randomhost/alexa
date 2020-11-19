<?php

namespace randomhost\Alexa\Response;

/**
 * Represents a LinkAccount response.
 *
 * @todo Figure out how this works.
 */
class LinkAccount
{
    public function render(): array
    {
        return [
            'type' => 'LinkAccount',
        ];
    }
}
