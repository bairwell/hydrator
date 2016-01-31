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

namespace Bairwell\Hydrator\Annotations;

/**
 * Used to annotate items should be type cast to a stringed UUID.
 *
 * @Annotation
 * @Target({"PROPERTY","ANNOTATION"})
 */
final class AsUuid extends AsBase
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

        // remove dashes
        $value = str_replace('-', '', strtolower(strval($value)));

        // remove anything else
        $replaced = preg_replace('/[^0-9a-f]/', '', $value);

        if ($value !== $replaced) {
            $this->setError(self::UUID_INVALID_CHARACTERS);
            return $defaultValue;
        }

        if (32 !== strlen($replaced)) {
            $this->setError(self::UUID_WRONG_LENGTH);
            return $defaultValue;
        }

        // add the dashes in to the appropriate section.
        $value = substr($replaced, 0, 8).
                 '-'.substr($replaced, 8, 4).
                 '-'.substr($replaced, (8 + 4), 4).
                 '-'.substr($replaced, (8 + 4 + 4), 4).
                 '-'.substr($replaced, (8 + 4 + 4 + 4), 12);
        return $value;
    }//end doCast()
}//end class
