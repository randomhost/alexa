<?php

namespace randomhost\Alexa\Response;

use InvalidArgumentException;

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
     * Valid card types.
     *
     * @var array
     */
    protected $validTypes
        = array(
            self::TYPE_SIMPLE,
            self::TYPE_STANDARD,
            self::TYPE_LINK_ACCOUNT,
        );

    /**
     * Type of card to render.
     *
     * Must be one of the self::TYPE_* constants.
     *
     * @var string
     */
    protected $type = self::TYPE_SIMPLE;

    /**
     * Title of the card.
     *
     * Not applicable for cards of type self::TYPE_LINK_ACCOUNT.
     *
     * @var string
     */
    protected $title = '';

    /**
     * Contents of a card.
     *
     * Not applicable for cards of self::TYPE_LINK_ACCOUNT.
     *
     * @var string
     */
    protected $content = '';

    /**
     * Returns the card type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the card type.
     *
     * @param string $type One of the self::TYPE_* constants.
     *
     * @return Card
     */
    public function setType($type)
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
     * Returns the card title.
     *
     * Not applicable for cards of type self::TYPE_LINK_ACCOUNT.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the card title.
     *
     * Not applicable for cards of type self::TYPE_LINK_ACCOUNT.
     *
     * @param string $title Card title.
     *
     * @return Card
     */
    public function setTitle($title)
    {
        $this->title = (string)$title;

        return $this;
    }

    /**
     * Returns the card content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Sets the card content.
     *
     * @param string $content Card content.
     *
     * @return Card
     */
    public function setContent($content)
    {
        $this->content = (string)$content;

        return $this;
    }

    /**
     * Returns the card data array.
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
        } elseif ($this->type == self::TYPE_STANDARD) {
            $response['text'] = $this->content;
        }

        return $response;
    }
}
