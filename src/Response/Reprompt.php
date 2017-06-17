<?php

namespace randomhost\Alexa\Response;

/**
 * Represents a Reprompt response.
 */
class Reprompt
{
    public $outputSpeech;

    public function __construct()
    {
        $this->outputSpeech = new OutputSpeech;
    }

    public function render()
    {
        return array(
            'outputSpeech' => $this->outputSpeech->render(),
        );
    }
}
