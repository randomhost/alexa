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
    public const TYPE_SIMPLE = 'Simple';

    /**
     * Type "Standard": A card that contains a title, text content, and an image to display.
     */
    public const TYPE_STANDARD = 'Standard';

    /**
     * Type "LinkAccount": A card that displays a link to an authorization URL that the user can
     * use to link their Alexa account with a user in another system.
     */
    public const TYPE_LINK_ACCOUNT = 'LinkAccount';

    /**
     * Valid card types.
     *
     * @var array
     */
    protected $validTypes
        = [
            self::TYPE_SIMPLE,
            self::TYPE_STANDARD,
            self::TYPE_LINK_ACCOUNT,
        ];

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
     * Not applicable for cards of type self::TYPE_LINK_ACCOUNT.
     *
     * @var string
     */
    protected $content = '';

    /**
     * Image of a card.
     *
     * Only applicable for cards of type self::TYPE_STANDARD.
     *
     * @var Image
     */
    protected $image;

    /**
     * Returns the card type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets the card type.
     *
     * @param string $type One of the self::TYPE_* constants.
     *
     * @return $this
     */
    public function setType(string $type): self
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
     */
    public function getTitle(): string
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
     * @return $this
     */
    public function setTitle($title): self
    {
        $this->title = (string) $title;

        return $this;
    }

    /**
     * Returns the card content.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Sets the card content.
     *
     * @param string $content Card content.
     *
     * @return $this
     */
    public function setContent($content): self
    {
        $this->content = (string) $content;

        return $this;
    }

    /**
     * Returns the Image object.
     *
     * @return Image
     */
    public function getImage(): ?Image
    {
        return $this->image;
    }

    /**
     * Injects the Image object.
     *
     * @param Image $image Image instance.
     *
     * @return $this
     */
    public function setImage(Image $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Returns the card data array.
     */
    public function render(): array
    {
        $response = [];

        $response['type'] = $this->type;

        if (self::TYPE_LINK_ACCOUNT !== $this->type) {
            $response['title'] = $this->title;
        }

        if (self::TYPE_SIMPLE == $this->type) {
            $response['content'] = $this->content;
        } elseif (self::TYPE_STANDARD == $this->type) {
            $response['text'] = $this->content;

            if ($this->image instanceof Image) {
                $imageData = $this->image->render();
                if (!empty($imageData)) {
                    $response['image'] = $imageData;
                }
            }
        }

        return $response;
    }
}
