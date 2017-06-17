<?php

namespace randomhost\Alexa\Response;

/**
 * Represents a Card response object.
 */
class Card
{
    /**
     * Type "Simple": A card that contains a title and plain text content.
     */
    const TYPE_SIMPLE = 'Simple';

    /**
     * Type "Standard": A card that contains a title, text content, and an image to display.
     */
    const TYPE_STANDARD = 'Standard';

    /**
     * Type "LinkAccount": A card that displays a link to an authorization URL that the user can
     * use to link their Alexa account with a user in another system.
     */
    const TYPE_LINK_ACCOUNT = 'LinkAccount';

    /**
     * A string describing the type of card to render.
     *
     * Must be one of the self::TYPE_* constants.
     *
     * @var string
     */
    public $type = self::TYPE_SIMPLE;

    /**
     * A string containing the title of the card.
     *
     * Not applicable for cards of type self::TYPE_LINK_ACCOUNT.
     *
     * @var string
     */
    public $title = '';

    /**
     * A string containing the contents of a Simple card.
     *
     * Not applicable for cards of type self::TYPE_STANDARD or self::TYPE_LINK_ACCOUNT.
     *
     * @var string
     */
    public $content = '';

    /**
     * A string containing the text content for a Standard card.
     *
     * Not applicable for cards of type self::TYPE_SIMPLE or self::TYPE_LINK_ACCOUNT.
     *
     * @var string
     */
    public $text = '';

    /**
     *
     * @return array
     */
    public function render()
    {
        $response = array();

        $response['type'] = $this->type;

        if ($this->type !== self::TYPE_LINK_ACCOUNT) {
            $response['title'] = $this->title;
        }

        if ($this->type == self::TYPE_SIMPLE) {
            $response['content'] = $this->content;
        }

        if ($this->type == self::TYPE_STANDARD) {
            $response['text'] = $this->text;
        }

        return $response;
    }
}
