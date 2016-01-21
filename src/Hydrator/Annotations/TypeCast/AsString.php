<?php
/**
 * Hydration system annotation.
 *
 * Used to annotate items should be type cast to strings.
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
 * Used to annotate items should be type cast to strings.
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class AsString extends CastBase
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
     * @param mixed $value        Value to be casted and returned by reference.
     * @param mixed $defaultValue Value to be returned if not set.
     *
     * @return mixed New value (or default value if unmatched)
     * @throws \TypeError If default is not a string.
     */
    public function doCast($value, $defaultValue = null)
    {
        if (false === is_string($defaultValue) && null !== $defaultValue) {
            throw new \TypeError('DefaultValue must be a string for AsString casts');
        }

        if (false === is_string($value) && false === is_numeric($value)) {
            $this->setError(self::ONLY_STRINGS_NUMERICS);
            return $defaultValue;
        }

        $value = strval($value);
        return $value;
    }//end doCast()
}//end class
