<?php
/**
 * Hydration system annotation.
 * Used to annotate items should be type cast to ints.
 * Project homepage: https://github.com/bairwell/hydrator
 * (c) Richard Bairwell <richard@bairwell.com> of Bairwell Ltd http://bairwell.com
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator\Annotations\TypeCast;

/**
 * Used to annotate items should be type cast to ints.
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class AsInt extends CastBase
{

    /**
     * Allow null
     *
     * @var boolean
     */
    public $allowNull = true;
    /**
     * Minimum int value.
     *
     * @var integer
     */
    public $min = PHP_INT_MIN;

    /**
     * Maximum int value.
     *
     * @var integer
     */
    public $max = PHP_INT_MAX;
    /**
     * Thousands/Digits separator. To be stripped off.
     *
     * @var string
     */
    public $digitsSeparator = ',';

    /**
     * Cast an value to a specific type.
     * Public calls should be made to "cast" which then delegates to this method.
     *
     * @param mixed $value        Value to be casted and returned by reference.
     * @param mixed $defaultValue Value to be returned if not set.
     *
     * @return mixed New value (or default value if unmatched).
     * @throws \TypeError If defaultValue is of invalid type.
     * @throws \RuntimeException If Min/Max are invalid.
     */
    public function doCast($value, $defaultValue = null)
    {
        if (false === is_int($defaultValue) && null !== $defaultValue) {
            throw new \TypeError('DefaultValue must be an Int for AsInt casts');
        }

        if (false === is_int($this->min) || false === is_int($this->max)) {
            throw new \RuntimeException('AsInt min/max are invalid - must be ints');
        }

        if (null !== $defaultValue && ($defaultValue > $this->max || $defaultValue < $this->min)) {
            throw new \RuntimeException('Default value is not within min/max range for AsInt');
        }

        if (false === is_string($value) && false === is_numeric($value)) {
            $this->setError(self::ONLY_STRINGS_NUMERICS);

            return $defaultValue;
        }

        if (false === is_int($value)) {
            $options = [
                'options' => [
                    'default' => null
                ]
            ];
            $value   = filter_var($value, FILTER_VALIDATE_INT, $options);
            if (false === is_int($value)) {
                $this->setError(self::DECIMAL_MUST_BE_ACCEPTED_FORMAT);

                return $defaultValue;
            }
        }

        if ($value > $this->max || $value < $this->min) {
            $this->setError(self::DECIMAL_OUTSIDE_ACCEPTABLE_RANGE, ['%min%' => $this->min, '%max%' => $this->max]);

            return $defaultValue;
        }

        return $value;
    }//end doCast()
}//end class
