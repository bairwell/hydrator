<?php
/**
 * Hydration system annotation.
 *
 * Used to annotate items should be type cast to booleans.
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
 * Used to annotate items should be type cast to booleans.
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class AsBool extends CastBase
{
    /**
     * Allow null
     *
     * @var boolean
     */
    public $allowNull = true;
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
        if (false === is_bool($defaultValue) && null !== $defaultValue) {
            throw new \TypeError('DefaultValue must be a boolean for AsBool casts: got '.gettype($defaultValue));
        }

        if (true === $value || false === $value) {
            return $value;
        }

        if (false === is_string($value) && false === is_numeric($value)) {
            $this->setError(self::ONLY_BOOLS_STRINGS_NUMERICS);
            return $defaultValue;
        }

        $value = strtolower(trim((string) $value));
        // filter var allows "1","true","on" and "yes" for true
        // "0","false","off","no" and "" for false
        // FILTER_NULL_ON_FAILURE will return null otherwise
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if (false === is_bool($value)) {
            $this->setError(
                self::BOOL_MUST_BE_ACCEPTED_FORMAT,
                ['%trues%' => '1,true,on,yes',
                             '%falses%' => '0,false,off,no'
                            ]
            );
            return $defaultValue;
        }

        return $value;
    }//end doCast()
}//end class
