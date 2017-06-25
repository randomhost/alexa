<?php

namespace randomhost\Alexa\Response;

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
     */
    public function render()
    {
        return array(
            'outputSpeech' => $this->outputSpeech->render(),
        );
    }
}
