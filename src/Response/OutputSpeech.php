<?php

namespace randomhost\Alexa\Response;

use InvalidArgumentException;

/**
 * Represents an OutputSpeech object.
 */
class OutputSpeech
{
    /**
     * "PlainText": Indicates that the output speech is defined as plain text.
     */
    public const TYPE_PLAIN = 'PlainText';

    /**
     * "SSML": Indicates that the output speech is text marked up with SSML.
     */
    public const TYPE_SSML = 'SSML';

    /**
     * Valid output types.
     *
     * @var array
     */
    protected $validTypes
        = [
            self::TYPE_PLAIN,
            self::TYPE_SSML,
        ];

    /**
     * Type of output speech to render.
     *
     * Must be one of the self::TYPE_* constants.
     *
     * @var string
     */
    protected $type = self::TYPE_PLAIN;

    /**
     * Text to speak to the user.
     *
     * This can either be plain text or SSML markup, depending on the value of $this->type;
     *
     * @var string
     */
    protected $text = '';

    /**
     * Returns the type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets the output type.
     *
     * @param string $type Either self::TYPE_PLAIN or self::TYPE_SSML.
     *
     * @return $this
     */
    public function setType($type): self
    {
        if (!in_array($type, $this->validTypes)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported type %s',
                    var_export($type, true)
                )
            );
        }
        $this->type = $type;

        return $this;
    }

    /**
     * Returns the text to speak to the user.
     *
     * This can either be plain text or SSML markup, depending on the value of $this->type;
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Sets the text to speak to the user.
     *
     * This can either be plain text or SSML markup, depending on the value of $this->type;
     *
     * @param string $text Text to speak to the user.
     *
     * @return $this
     */
    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Returns the speech data array.
     */
    public function render(): array
    {
        switch ($this->type) {
            case self::TYPE_SSML:
                return [
                    'type' => $this->type,
                    'ssml' => $this->text,
                ];
            case self::TYPE_PLAIN:
            default:
                return [
                    'type' => $this->type,
                    'text' => $this->text,
                ];
        }
    }
}
