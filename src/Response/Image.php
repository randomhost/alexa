<?php

namespace randomhost\Alexa\Response;

use InvalidArgumentException;

/**
 * Represents a card image.
 */
class Image
{
    /**
     * Small card image URL.
     *
     * The recommended image size for small card images is 720w x 480h.
     *
     * @var string
     */
    protected $smallImageUrl = '';

    /**
     * Large card image URL.
     *
     * The recommended image size for small card images is 1200w x 800h.
     *
     * @var string
     */
    protected $largeImageUrl = '';

    /**
     * Returns the small card image URL.
     */
    public function getSmallImageUrl(): string
    {
        return $this->smallImageUrl;
    }

    /**
     * Sets the small card image URL.
     *
     * The recommended image size for small card images is 720w x 480h.
     *
     * @param string $imageUrl Small card image URL.
     *
     * @return $this
     */
    public function setSmallImageUrl(string $imageUrl): self
    {
        $this->validateImage($imageUrl);

        $this->smallImageUrl = $imageUrl;

        return $this;
    }

    /**
     * Sets the large card image URL.
     */
    public function getLargeImageUrl(): string
    {
        return $this->largeImageUrl;
    }

    /**
     * Returns the large card image URL.
     *
     * The recommended image size for small card images is 1200w x 800h.
     *
     * @param string $imageUrl Large card image URL.
     *
     * @return $this
     */
    public function setLargeImageUrl(string $imageUrl): self
    {
        $this->validateImage($imageUrl);

        $this->largeImageUrl = $imageUrl;

        return $this;
    }

    /**
     * Returns the card data array.
     */
    public function render(): array
    {
        $response = [];

        if (!empty($this->smallImageUrl)) {
            $response['smallImageUrl'] = $this->smallImageUrl;
        }

        if (!empty($this->largeImageUrl)) {
            $response['largeImageUrl'] = $this->largeImageUrl;
        }

        return $response;
    }

    /**
     * Validates the given image URL.
     *
     * @param string $imageUrl Image URL.
     *
     * @return $this
     */
    protected function validateImage(string $imageUrl): self
    {
        $protocol = parse_url($imageUrl, PHP_URL_SCHEME);
        if ('https' !== $protocol) {
            throw new InvalidArgumentException(
                'Images must be hosted via HTTPS'
            );
        }

        $path = parse_url($imageUrl, PHP_URL_PATH);
        if (empty($path)) {
            throw new InvalidArgumentException(
                'Image path cannot be empty'
            );
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if (!in_array(strtolower($extension), ['jpeg', 'jpg', 'png'])) {
            throw new InvalidArgumentException(
                'Images must be in JPG or PNG format'
            );
        }

        return $this;
    }
}
