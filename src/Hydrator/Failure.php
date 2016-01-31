<?php
/**
 * A failure report generated during hydration.
 *
 * Project homepage: https://github.com/bairwell/hydrator
 * (c) Richard Bairwell <richard@bairwell.com> of Bairwell Ltd http://bairwell.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator;

/**
 * Class Failure.
 *
 * A failure report generated during hydration.
 */
class Failure
{

    /**
     * The input field that we were reading.
     *
     * @var string
     */
    protected $inputField = '';

    /**
     * The tokenised failure message.
     *
     * @var string
     */
    protected $message = '';

    /**
     * Any tokens associated with the failure message.
     *
     * @var array
     */
    protected $tokens = [];

    /**
     * What we were reading from?
     *
     * @var string
     */
    protected $source = '';

    /**
     * The input value.
     *
     * @var mixed
     */
    protected $inputValue = null;

    /**
     * Failure constructor.
     */
    public function __construct()
    {
        $this->inputField = '';
        $this->message    = '';
        $this->tokens     = [];
        $this->source     = '';
        $this->inputValue = null;
    }//end __construct()

    /**
     * Ge the name of the source field we were reading from.
     *
     * @return string
     */
    public function getInputField() : string
    {
        return $this->inputField;
    }//end getInputField()


    /**
     * Set which source field we were reading from.
     *
     * @param string $inputField Name of the field we were reading from.
     *
     * @return self
     */
    public function setInputField(string $inputField) : self
    {
        $this->inputField = $inputField;

        return $this;
    }//end setInputField()


    /**
     * Get the failure message.
     *
     * @return string
     */
    public function getMessage() : string
    {
        return $this->message;
    }//end getMessage()


    /**
     * Set the failure message.
     *
     * @param string $message Set the message (which could use tokens).
     *
     * @return self
     */
    public function setMessage(string $message) : self
    {
        $this->message = $message;

        return $this;
    }//end setMessage()


    /**
     * Get the message tokens.
     *
     * @return array
     */
    public function getTokens() : array
    {
        return $this->tokens;
    }//end getTokens()


    /**
     * Set the message tokens.
     *
     * @param array $tokens Message tokens and values.
     *
     * @return self
     */
    public function setTokens(array $tokens) : self
    {
        $this->tokens = $tokens;

        return $this;
    }//end setTokens()


    /**
     * Get the source we were reading from.
     *
     * @return string
     */
    public function getSource() : string
    {
        return $this->source;
    }//end getSource()


    /**
     * Which source were we reading from?
     *
     * @param string $source Where we were reading from.
     *
     * @return self
     */
    public function setSource(string $source) : self
    {
        $this->source = $source;

        return $this;
    }//end setSource()


    /**
     * Get the input value.
     *
     * @return mixed
     */
    public function getInputValue()
    {
        return $this->inputValue;
    }//end getInputValue()


    /**
     * Set the input value.
     *
     * @param mixed $inputValue The input value we rejected.
     *
     * @return self
     */
    public function setInputValue($inputValue) : self
    {
        $this->inputValue = $inputValue;

        return $this;
    }//end setInputValue()
}//end class
