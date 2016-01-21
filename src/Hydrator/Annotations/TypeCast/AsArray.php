<?php
/**
 * Hydration system annotation.
 *
 * Used to annotate items should be type cast to an array.
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
 * Used to annotate items should be type cast to an array.
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class AsArray extends CastBase
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
        if (false === is_array($defaultValue) && null !== $defaultValue) {
            throw new \TypeError('DefaultValue must be an array for AsArray casts');
        }

        if (true === is_array($value)) {
            return $value;
        } else {
            $value = [$value];
            return $value;
        }
    }//end doCast()
}//end class
