<?php

namespace randomhost\Alexa\Response;

/**
 * Represents an OutputSpeech object.
 */
class OutputSpeech
{
    /**
     * "PlainText": Indicates that the output speech is defined as plain text.
     */
    const TYPE_PLAIN = 'PlainText';

    /**
     * "SSML": Indicates that the output speech is text marked up with SSML.
     */
    const TYPE_SSML = 'SSML';

    /**
     * A string containing the type of output speech to render.
     *
     * Must be one of the self::TYPE_* constants.
     *
     * @var string
     */
    public $type = self::TYPE_PLAIN;

    /**
     * A string containing the speech to render to the user.
     *
     * Use this when type is self::TYPE_PLAIN.
     *
     * @var string
     */
    public $text = '';

    /**
     * A string containing text marked up with SSML to render to the user.
     *
     * Use this when type is self::TYPE_SSML.
     *
     * @var string
     */
    public $ssml = '';

    /**
     * @return array
     */
    public function render()
    {
        switch ($this->type) {
            case self::TYPE_PLAIN:
                return array(
                    'type' => $this->type,
                    'text' => $this->text,
                );
            case self::TYPE_SSML:
                return array(
                    'type' => $this->type,
                    'ssml' => $this->ssml,
                );
        }
    }
}
