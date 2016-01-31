<?php
/**
 * Hydration system annotation.
 *
 * Base cast system.
 *
 * Project homepage: https://github.com/bairwell/hydrator
 * (c) Richard Bairwell <richard@bairwell.com> of Bairwell Ltd http://bairwell.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator\Annotations;

/**
 * Class AsBase.
 *
 * Base class for all typeCast classes to inherit from.
 */
abstract class AsBase
{

    const ONLY_STRINGS_NUMERICS = 'Only strings or numerics are accepted';

    const ONLY_BOOLS_STRINGS_NUMERICS = 'Only booleans, strings or numerics are accepted';

    const BOOL_MUST_BE_ACCEPTED_FORMAT = 'Value must either be the boolean true (or %trues%) or false (or %falses%)';

    const DATETIME_MUST_BE_ACCEPTED_FORMAT = 'Unable to interpret as a valid date time in expected range - '.
                                             'expected either an '.
                                             'ISO8601/RFC3339 or RFC-2822 formatted date or unix datestamp';

    const DATETIME_OUTSIDE_ACCEPTABLE_RANGE = 'Date must be between %min% and %max%';

    const FLOAT_MUST_BE_ACCEPTED_FORMAT    = 'Value must be a float using the decimal separator '.
                                             '%decimalSeparator% and the optional digits separator %digitsSeparator%';
    const DECIMAL_MUST_BE_ACCEPTED_FORMAT  = 'Value must be a decimal using the optional digits '.
                                             'separator %digitsSeparator%';
    const DECIMAL_OUTSIDE_ACCEPTABLE_RANGE = 'Decimal must be between %min% and %max%';

    const UUID_INVALID_CHARACTERS = 'Uuid contains invalid characters';

    const UUID_WRONG_LENGTH = 'Uuid is the wrong length';

    const ARRAY_CONTENTS_INVALID = 'Array contains invalid items - only %validCount% out of %totalCount%'.
                                 'items allowed. Acceptable items keys: "%validItemsList%", '.
                                   'Invalid items keys: "%invalidItemsList%"';
    /**
     * Allow null.
     *
     * This comment (and the variable/property) must be included in any cast which accepts nulls
     * to enable Doctrine Annotations to read them.
     *
     * @var boolean
     */
    public $allowNull = false;

    /**
     * Last error encountered.
     *
     * @var string|null
     */
    private $lastError = null;

    /**
     * Tokens for the last error (if any).
     *
     * @var array
     */
    private $lastErrorTokens = [];

    /**
     * Set the error
     *
     * @param string|null $error  Error message.
     * @param array       $tokens List of tokens for translation placeholders.
     *
     * @return self
     */
    final protected function setError(string $error = null, array $tokens = []) : self
    {
        $this->lastError       = $error;
        $this->lastErrorTokens = $tokens;
        return $this;
    }//end setError()


    /**
     * Cast an value to a specific type.
     *
     * This public function resets the errors, checks for allowNull settings
     * before passing to doCast.
     *
     * @param mixed $value        Value to be casted and returned by reference.
     * @param mixed $defaultValue Value to be returned if not set.
     *
     * @return mixed New value (or default value if unmatched)
     * @throws \RuntimeException If no default value is passed,but null is not allowed.
     */
    final public function cast($value, $defaultValue = null)
    {
        $this->setError(null);
        if (false === is_bool($this->allowNull)) {
            throw new \RuntimeException('AllowNull is not boolean on cast');
        }

        if (false === $this->allowNull && null === $defaultValue) {
            throw new \RuntimeException('If the defaultValue is null, nulls must be allowed on cast');
        }

        if (null === $value && true === $this->allowNull) {
            return null;
        }

        $value = $this->doCast($value, $defaultValue);
        return $value;
    }//end cast()


    /**
     * Cast an value to a specific type.
     *
     * Public calls should be made to "cast" which then delegates to this method.
     *
     * @param mixed $value        Value to be casted and returned by reference.
     * @param mixed $defaultValue Value to be returned if not set.
     *
     * @return mixed New value (or default value if unmatched).
     * @throws \TypeError If defaultValue is of invalid type.
     */
    abstract protected function doCast($value, $defaultValue);
    /**
     * Have we errored?
     *
     * @return boolean
     */
    final public function hasErrored() : bool
    {
        if (null === $this->lastError) {
            return false;
        }

        return true;
    }//end hasErrored()

    /**
     * Get the error message appropriate for when we are unable to cast.
     *
     * @return string
     */
    final public function getErrorMessage() : string
    {
        return $this->lastError;
    }//end getErrorMessage()


    /**
     * Get the error message tokens which should correspond with the failure message.
     *
     * @return array
     */
    final public function getErrorTokens() : array
    {
        return $this->lastErrorTokens;
    }//end getErrorTokens()
}//end class
