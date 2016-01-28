<?php
/**
 * A cachable copy of a class's properties to avoid having to read annotations all the time.
 *
 * Project homepage: https://github.com/bairwell/hydrator
 * (c) Richard Bairwell <richard@bairwell.com> of Bairwell Ltd http://bairwell.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator;

use Bairwell\Hydrator\Annotations\From;
use Bairwell\Hydrator\Annotations\AsBase;

/**
 * Class CachedProperty.
 *
 * A cachable copy of a class's properties to avoid having to read annotations all the time.
 */
class CachedProperty
{

    /**
     * Class Name.
     *
     * @var string
     */
    protected $className = '';

    /**
     * Property Name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Cast as.
     *
     * @var AsBase|null $castAs
     */
    protected $castAs = null;

    /**
     * Where are we hydrating from.
     *
     * @var From $from
     */
    protected $from;

    /**
     * CachedProperty constructor.
     *
     * @param string        $className    Name of the class for this property.
     * @param string        $propertyName Name of the property.
     * @param From   $from         Where we are hydrating from.
     * @param AsBase|null $castAs       Any cast setting.
     */
    public function __construct(string $className, string $propertyName, From $from, AsBase $castAs = null)
    {
        $this->setClassName($className);
        $this->setName($propertyName);
        $this->setCastAs($castAs);
        $this->setFrom($from);
    }//end __construct()

    /**
     * Get the name of this property.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }//end getName()


    /**
     * Set the name of this property.
     *
     * @param string $name Property name.
     *
     * @return $this
     */
    public function setName(string $name) : self
    {
        $this->name = $name;

        return $this;
    }//end setName()


    /**
     * What should we cast as?
     *
     * @return AsBase
     */
    public function getCastAs() : AsBase
    {
        return $this->castAs;
    }//end getCastAs()


    /**
     * Have we got a cast as option?
     *
     * @return boolean
     */
    public function hasCastAs() : bool
    {
        if (null === $this->castAs) {
            return false;
        }

        return true;
    }//end hasCastAs()


    /**
     * What should we cast as?
     *
     * @param AsBase|null $as What, if anything, should we be typecasting to.
     *
     * @return self
     */
    public function setCastAs(AsBase $as = null) : self
    {
        $this->castAs = $as;

        return $this;
    }//end setCastAs()


    /**
     * Return where we are hydrating from.
     *
     * @return From
     */
    public function getFrom() : From
    {
        return $this->from;
    }//end getFrom()


    /**
     * Set where we are hydrating from.
     *
     * @param From $from The item we are hydrating from.
     *
     * @return self
     */
    public function setFrom(From $from) : self
    {
        $this->from = $from;

        return $this;
    }//end setFrom()


    /**
     * Get the class name.
     *
     * @return string
     */
    public function getClassName() : string
    {
        return $this->className;
    }//end getClassName()


    /**
     * Set the class name.
     *
     * @param string $className Set the class name.
     *
     * @return self
     */
    public function setClassName(string $className) : self
    {
        $this->className = $className;

        return $this;
    }//end setClassName()
}//end class
