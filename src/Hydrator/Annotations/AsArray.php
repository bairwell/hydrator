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

namespace Bairwell\Hydrator\Annotations;

/**
 * Used to annotate items should be type cast to an array.
 *
 * @Annotation
 * @Target({"PROPERTY","ANNOTATION"})
 */
final class AsArray extends AsBase
{
    /**
     * Allow null
     *
     * @var boolean
     */
    public $allowNull = true;

    /**
     * What sort of types should this array be allowed to contain?
     *
     * @var array
     */
    public $of = [];
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

        if (false === is_array($value)) {
            $value = [$value];
        }

        // now need to check the array contains appropriate values
        if (false === empty($this->of)) {
            $returned     = [];
            $validItems   = [];
            $invalidItems = [];
            $count        = 0;
            foreach ($value as $singleValue) {
                $thisNew = null;
                /* @var AsBase $singleOf */
                foreach ($this->of as $singleOf) {
                    if (false === ($singleOf instanceof AsBase)) {
                        throw new \TypeError('Unrecognised cast type for array');
                    }

                    $cast = $singleOf->cast($singleValue, null);
                    if (null !== $cast && false === $singleOf->hasErrored()) {
                        $thisNew = $cast;
                        break;
                    }
                }

                if (null !== $thisNew) {
                    $returned[]   = $thisNew;
                    $validItems[] = $count;
                } else {
                    $invalidItems[] = $count;
                }

                $count++;
            }//end foreach

            if (count($returned) !== count($value)) {
                $this->setError(
                    self::ARRAY_CONTENTS_INVALID,
                    ['%validCount%' => count($returned),
                                 '%totalCount%' => count($value),
                                 '%validItemsList%' => implode(',', $validItems),
                                 '%invalidItemsList%' => implode(',', $invalidItems)]
                );
                return $defaultValue;
            } else {
                $value = $returned;
            }
        }//end if

        return $value;
    }//end doCast()
}//end class
