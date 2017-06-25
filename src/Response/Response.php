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
    protected $version = '1.0';

    /**
     * Session attributes array.
     *
     * @var array
     */
    protected $sessionAttributes = array();

    /**
     * OutputSpeech instance.
     *
     * @var null|OutputSpeech
     */
    protected $outputSpeech = null;

    /**
     * Card instance.
     *
     * @var null|Card
     */
    protected $card = null;

    /**
     * LinkAccount instance.
     *
     * @var null|LinkAccount
     */
    protected $linkAccount = null;

    /**
     * Reprompt instance.
     *
     * @var null|Reprompt
     */
    protected $reprompt = null;

    /**
     * Defines whether the session should be ended after this response.
     *
     * @var bool
     */
    protected $shouldEndSession = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->outputSpeech = new OutputSpeech;
    }

    /**
     * Set up plain text response.
     *
     * @param string $text Plain text.
     *
     * @return $this
     */
    public function respond($text)
    {
        $this->outputSpeech = new OutputSpeech;
        $this->outputSpeech->setType(OutputSpeech::TYPE_PLAIN);
        $this->outputSpeech->setText($text);

        return $this;
    }

    /**
     * Set up response with SSML.
     *
     * @param string $ssml Text with SSML markup.
     *
     * @return $this
     */
    public function respondSSML($ssml)
    {
        $this->outputSpeech = new OutputSpeech;
        $this->outputSpeech->setType(OutputSpeech::TYPE_SSML);
        $this->outputSpeech->setText($ssml);

        return $this;
    }

    /**
     * Set up reprompt with plain text.
     *
     * @param string $text Plain text.
     *
     * @return $this
     */
    public function reprompt($text)
    {
        $outputSpeech = $this->getOutputSpeechInstance(
            OutputSpeech::TYPE_PLAIN,
            $text
        );

        $this->reprompt = new Reprompt($outputSpeech);

        return $this;
    }

    /**
     * Set up reprompt with SSML.
     *
     * @param string $ssml Text with SSML markup.
     *
     * @return $this
     */
    public function repromptSSML($ssml)
    {
        $outputSpeech = $this->getOutputSpeechInstance(
            OutputSpeech::TYPE_SSML,
            $ssml
        );

        $this->reprompt = new Reprompt($outputSpeech);

        return $this;
    }

    /**
     * Add card information.
     *
     * @param string $title   Card title.
     * @param string $content Card content.
     *
     * @return $this
     */
    public function withCard($title, $content = '')
    {
        $this->card = new Card;
        $this->card
            ->setTitle($title)
            ->setContent($content);

        return $this;
    }

    /**
     * Add link account information.
     *
     * @return $this
     */
    public function withLinkAccount()
    {
        $this->linkAccount = new LinkAccount;

        return $this;
    }

    /**
     * Set if the session should end after the response.
     *
     * @param bool $shouldEndSession True / false.
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
     * @param string $key   Attribute key.
     * @param mixed  $value Attribute value.
     */
    public function addSessionAttribute($key, $value)
    {
        $this->sessionAttributes[$key] = $value;
    }

    /**
     * Return the response data array.
     *
     * @return array
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

    /**
     * Returns a new OutputSpeech instance configured with the given parameters.
     *
     * @param string $type One of the OutputSpeech::TYPE_* constants.
     * @param string $text Text to speak to the user.
     *
     * @return OutputSpeech
     */
    private function getOutputSpeechInstance($type, $text)
    {
        $outputSpeech = new OutputSpeech();
        $outputSpeech->setType($type);
        $outputSpeech->setText($text);

        return $outputSpeech;
    }
}
