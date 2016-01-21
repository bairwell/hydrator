<?php
/**
 * Hydration system annotation.
 *
 * Used to annotate items should be type cast to floats.
 *
 * Project homepage: https://github.com/bairwell/hydrator
 * (c) Richard Bairwell <richard@bairwell.com> of Bairwell Ltd http://bairwell.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator\Annotations\TypeCast;

/**
 * Used to annotate items should be type cast to floats.
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class AsFloat extends CastBase
{
    /**
     * Allow null
     *
     * @var boolean
     */
    public $allowNull = true;
    /**
     * When round to this position. Null (default) means do not round
     *
     * @var integer
     */
    public $precision = null;

    /**
     * Decimal separator.
     *
     * @var string
     */
    public $decimalSeparator = '.';

    /**
     * Thousands/Digits separator. To be stripped off.
     *
     * @var string
     */
    public $digitsSeparator = ',';
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
    public function doCast($value, $defaultValue = null)
    {
        if (false === is_float($defaultValue) && null !== $defaultValue) {
            throw new \TypeError('DefaultValue must be a float for AsFloat casts');
        }

        if (false === is_string($value) && false === is_numeric($value)) {
            $this->setError(self::ONLY_STRINGS_NUMERICS);
            return $defaultValue;
        }

        if (false === is_float($value)) {
            $options = [
                'options' => [
                    'default' => null,
                    'decimal' => $this->decimalSeparator
                ]
            ];
            // remove thousands separator
            $value = str_replace($this->digitsSeparator, '', strval($value));
            $value = filter_var($value, FILTER_VALIDATE_FLOAT, $options);
        }

        if (false === is_float($value) || true === is_nan($value) || false === is_finite($value)) {
            $this->setError(
                self::FLOAT_MUST_BE_ACCEPTED_FORMAT,
                ['decimalSeparator' => $this->decimalSeparator,
                             'digitsSeparator' => $this->digitsSeparator
                ]
            );
            return $defaultValue;
        }


        if (null !== $this->precision) {
            $value = round($value, $this->precision);
        }

        return $value;
    }//end doCast()
}//end class
