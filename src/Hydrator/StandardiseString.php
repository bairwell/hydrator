<?php
/**
 * Standardise String.
 *
 * Project homepage: https://github.com/bairwell/hydrator
 * (c) Richard Bairwell <richard@bairwell.com> of Bairwell Ltd http://bairwell.com
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator;

trait StandardiseString {

    /**
     * Standardise a string. Converts XyZ_abc-def to xyzAbcDef.
     *
     * @param string $string Input string.
     *
     * @return string Standardised string.
     * @throws \Exception If the string ends up being less than 1 character in length.
     */
    protected function standardiseString(string $string) : string
    {
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9_\- ]/', '', $string);
        $string = strtr($string, '_-', '  '); // replace _ and - with spaces.
        $string = ucwords($string);
        $string = str_replace(' ', '', $string); // remove spaces
        $string = lcfirst($string); // lower case first character
        if (strlen($string) < 1) {
            throw new \Exception('Unable to standardise string - ended up too short');
        }

        return $string;
    }//end unsetAllHydrationSources()
}
