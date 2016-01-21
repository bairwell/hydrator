<?php
/**
 * A cachable copy of a class to avoid having to read annotations all the time.
 *
 * Project homepage: https://github.com/bairwell/hydrator
 * (c) Richard Bairwell <richard@bairwell.com> of Bairwell Ltd http://bairwell.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator;

use Bairwell\Hydrator\CachedProperty;

/**
 * A cachable copy of a class to avoid having to read annotations all the time.
 */
class CachedClass implements \Iterator, \ArrayAccess, \Countable
{

    /**
     * Our collection of properties.
     *
     * @var CachedProperty[]
     */
    protected $properties = [];

    /**
     * Our class name.
     *
     * @var string
     */
    protected $className = '';

    /**
     * Current iterator position.
     *
     * @var integer
     */
    private $position = 0;

    /**
     * CachedClass constructor.
     *
     * @param string $className Name of the class.
     */
    public function __construct(string $className = '')
    {
        $this->className  = $className;
        $this->properties = [];
        $this->position   = 0;
    }//end __construct()


    /**
     * Get the class name.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->className;
    }//end getName()



    /**
     * Count elements of an object.
     *
     * @link   http://php.net/manual/en/countable.count.php
     * @return integer The custom count as an integer.
     */
    public function count() : int
    {
        $return = count($this->properties);
        return $return;
    }//end count()


    /**
     * Return the current element.
     *
     * @link   http://php.net/manual/en/iterator.current.php
     * @return CachedProperty[]
     * @since  5.0.0
     */
    public function current() : array
    {
        $return = current($this->properties);
        return $return;
    }//end current()


    /**
     * Move forward to next element.
     *
     * @link http://php.net/manual/en/iterator.next.php
     */
    public function next()
    {
        next($this->properties);
    }//end next()


    /**
     * Return the key of the current element.
     *
     * @link   http://php.net/manual/en/iterator.key.php
     * @return string|null
     */
    public function key()
    {
        $return = key($this->properties);
        return $return;
    }//end key()


    /**
     * Checks if current position is valid.
     *
     * @link   http://php.net/manual/en/iterator.valid.php
     * @return boolean Returns true on success or false on failure.
     */
    public function valid() : bool
    {
        $key = key($this->properties);
        if (null === $key) {
            return false;
        } else {
            return true;
        }
    }//end valid()


    /**
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind()
    {
        reset($this->properties);
    }//end rewind()


    /**
     * Whether a offset exists

     * @param mixed $offset An offset to check for.
     *
     * @return boolean true on success or false on failure.
     * @throws \TypeError If offset is not a string of the property name.
     * @link   http://php.net/manual/en/arrayaccess.offsetexists.php
     */
    public function offsetExists($offset) : bool
    {
        if (false === is_string($offset)) {
            throw new \TypeError('Offset must be a propertyName string');
        }

        if (true === isset($this->properties[$offset])) {
            return true;
        } else {
            return false;
        }
    }//end offsetExists()

    /**
     * Offset to retrieve
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return CachedProperty[]|null
     * @throws \TypeError If offset is not a string of the property name.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     */
    public function offsetGet($offset)
    {
        if (false === is_string($offset)) {
            throw new \TypeError('Offset must be a propertyName string');
        }

        if (true === isset($this->properties[$offset])) {
            return $this->properties[$offset];
        } else {
            return null;
        }
    }//end offsetGet()

    /**
     * Add a property onto our list.
     *
     * @param \Bairwell\Hydrator\CachedProperty $value The property we are adding/stacking.
     *
     * @return CachedClass
     *
     * @throws \TypeError If class of property does not match this class.
     */
    public function add(CachedProperty $value) : self
    {
        $offset = $value->getName();

        if ($value->getClassName() !== $this->className) {
            throw new \TypeError(
                'Cannot add property "'.$offset.'" for class '.
                                 $value->getClassName().' to class '.$this->className
            );
        }

        if (false === isset($this->properties[$offset])) {
            $this->properties[$offset] = [];
        }

        $this->properties[$offset][] = $value;
        return $this;
    }//end add()

    /**
     * Get a specific property.
     *
     * @param string $offset The name of the property.
     *
     * @return CachedClass[]
     */
    public function get(string $offset) : array
    {
        if (false === isset($this->properties[$offset])) {
            return [];
        }

        return $this->properties[$offset];
    }//end get()

    /**
     * Offset to set.
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     *
     * @throws \TypeError If the value is not a cached property.
     * @throws \RuntimeException If offset already exists.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     */
    public function offsetSet($offset, $value)
    {
        if (false === is_string($offset)) {
            throw new \TypeError('Offset must be a propertyName string');
        }

        if (false === ($value instanceof CachedProperty)) {
            $message = 'Value must be a CachedProperty: got '.gettype($value);
            if (true === is_object($value)) {
                $message .= ' '.get_class($value);
            }

            throw new \TypeError($message);
        }

        if (true === isset($this->properties[$offset])) {
            throw new \RuntimeException('Offset '.$offset.' already exists');
        }

        $this->add($value);
    }//end offsetSet()


    /**
     * Offset to unset
     *
     * @param mixed $offset The offset to unset.
     *
     * @throws \TypeError If offset is not a string of the property name.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     */
    public function offsetUnset($offset)
    {
        if (false === is_string($offset)) {
            throw new \TypeError('Offset must be a propertyName string');
        }

        unset($this->properties[$offset]);
    }//end offsetUnset()
}//end class
