<?php
/**
 * Hydration system annotation.
 *
 * Used to annotate items that require hydration.
 *
 * Project homepage: https://github.com/bairwell/hydrator
 * (c) Richard Bairwell <richard@bairwell.com> of Bairwell Ltd http://bairwell.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bairwell\Hydrator\Annotations;
use Doctrine\Common\Annotations\Annotation\Required;

/**
 * Hydration system annotation.
 *
 * Used to annotate items that require hydration.
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class From
{

    /**
     * Which source(s) should this property be fed from?
     *
     * @var      array
     * @Required
     */
    public $sources = [];

    /**
     * Which field should this property be read from?
     * If not set, defaults to the property name.
     *
     * @var string
     */
    public $field = null;

    /**
     * List of conditions which need to be met before this property is hydrated.
     *
     * @var array
     */
    public $conditions = [];

    /**
     * List of available styles to parse array data from. Will allow single items in.
     *
     * If the hydrator matches an early entries, it will stop scanning.
     *
     * Values: basic (PHP array), csv (foo,bar), ssv (foo bar), tsb(foo\tbar), pipes (foo|bar)
     *
     * @var array
     */
    public $arrayStyles = [];
}//end class
