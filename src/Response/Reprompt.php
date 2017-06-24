<?php

namespace randomhost\Alexa\Response;

use RuntimeException;

/**
 * Represents a Reprompt response.
 */
class Reprompt
{
    /**
     * OutputSpeech instance.
     *
     * @var OutputSpeech
     */
    protected $outputSpeech;

    /**
     * Reprompt constructor.
     *
     * @param OutputSpeech $outputSpeech OutputSpeech instance.
     */
    public function __construct(OutputSpeech $outputSpeech)
    {
        $this->outputSpeech = $outputSpeech;
    }

    /**
     * Returns the Reprompt data array.
     *
     * @return array
     *
     * @throws RuntimeException Thrown if no OutputSpeech instance has been injected.
     */
    public function render()
    {
        return array(
            'outputSpeech' => $this->outputSpeech->render(),
        );
    }
}
