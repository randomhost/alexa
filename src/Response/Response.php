<?php

namespace randomhost\Alexa\Response;

/**
 * Represents a Response.
 */
class Response
{
    /**
     * Response format version.
     *
     * @var string
     */
    public $version = '1.0';

    /**
     * Session attributes array.
     *
     * @var array
     */
    public $sessionAttributes = array();

    /**
     * OutputSpeech instance.
     *
     * @var null|OutputSpeech
     */
    public $outputSpeech = null;

    /**
     * Card instance.
     *
     * @var null|Card
     */
    public $card = null;

    /**
     * LinkAccount instance.
     *
     * @var null|LinkAccount
     */
    public $linkAccount = null;

    /**
     * Reprompt instance.
     *
     * @var null|Reprompt
     */
    public $reprompt = null;

    /**
     * Defines whether the session should be ended after this response.
     *
     * @var bool
     */
    public $shouldEndSession = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->outputSpeech = new OutputSpeech;
    }

    /**
     * Set output speech as text
     *
     * @param string $text
     *
     * @return $this
     */
    public function respond($text)
    {
        $this->outputSpeech = new OutputSpeech;
        $this->outputSpeech->text = $text;

        return $this;
    }

    /**
     * Set up response with SSML.
     *
     * @param string $ssml
     *
     * @return $this
     */
    public function respondSSML($ssml)
    {
        $this->outputSpeech = new OutputSpeech;
        $this->outputSpeech->type = 'SSML';
        $this->outputSpeech->ssml = $ssml;

        return $this;
    }

    /**
     * Set up reprompt with given text
     *
     * @param string $text
     *
     * @return $this
     */
    public function reprompt($text)
    {
        $this->reprompt = new Reprompt;
        $this->reprompt->outputSpeech->text = $text;

        return $this;
    }

    /**
     * Set up reprompt with given ssml
     *
     * @param string $ssml
     *
     * @return $this
     */
    public function repromptSSML($ssml)
    {
        $this->reprompt = new Reprompt;
        $this->reprompt->outputSpeech->type = 'SSML';
        $this->reprompt->outputSpeech->text = $ssml;

        return $this;
    }

    /**
     * Add card information
     *
     * @param string $title
     * @param string $content
     *
     * @return $this
     */
    public function withCard($title, $content = '')
    {
        $this->card = new Card;
        $this->card->title = $title;
        $this->card->content = $content;

        return $this;
    }

    /**
     * Add link account information
     *
     * @return $this
     */
    public function withLinkAccount()
    {
        $this->linkAccount = new LinkAccount;

        return $this;
    }

    /**
     * Set if it should end the session
     *
     * @param bool $shouldEndSession
     *
     * @return $this
     */
    public function endSession($shouldEndSession = true)
    {
        $this->shouldEndSession = $shouldEndSession;

        return $this;
    }

    /**
     * Add a session attribute that will be passed in every requests.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function addSessionAttribute($key, $value)
    {
        $this->sessionAttributes[$key] = $value;
    }

    /**
     * Return the response as an array for JSON-ification
     *
     * @return type
     */
    public function render()
    {
        $cardObject = $this->card ? $this->card : $this->linkAccount;

        return array(
            'version' => $this->version,
            'sessionAttributes' => $this->sessionAttributes,
            'response' => array(
                'outputSpeech' => $this->outputSpeech ? $this->outputSpeech->render() : null,
                'card' => $cardObject ? $cardObject->render() : null,
                'reprompt' => $this->reprompt ? $this->reprompt->render() : null,
                'shouldEndSession' => $this->shouldEndSession ? true : false,
            ),
        );
    }
}
