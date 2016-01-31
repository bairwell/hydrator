<?php
/**
 * Hydrator Conditionals.
 *
 * Project homepage: https://github.com/bairwell/hydrator
 * (c) Richard Bairwell <richard@bairwell.com> of Bairwell Ltd http://bairwell.com
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator;

/**
 * Class Conditionals.
 * Used to hold a collection of hydration Conditionals.
 */
class Conditionals implements \Iterator, \Countable, \ArrayAccess
{

    use StandardiseString;

    /**
     * Our collection of conditionals.
     *
     * @var array $conditionals
     */
    protected $conditionals = [];

    /**
     * Current iterator position.
     *
     * @var integer
     */
    protected $position = 0;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->conditionals = [];
        $this->position     = 0;
    }//end __construct()


    /**
     * Add one or more conditionals.
     *
     * @param array|string $name        Name of the conditionals(s) to add.
     * @param callable     $conditional Actual conditional.
     *
     * @return self
     * @throws \TypeError If conditional is not valid.
     */
    public function add($name, callable $conditional) : self
    {
        if (true === is_array($name)) {
            // recursively add all the source names.
            foreach ($name as $subName) {
                $this->add($subName, $conditional);
            }

            return $this;
        }

        if (false === is_string($name)) {
            throw new \TypeError('Name must be a string or an array');
        }

        $this->offsetSet($name, $conditional);
        return $this;
    }//end add()


    /**
     * Removes one or more hydration conditionals.
     *
     * @param string|array $names Name(s) of the hydration conditional(s) to remove.
     *
     * @throws \TypeError If names is not an array or string.
     */
    public function unset($names)
    {
        $removed = 0;
        if (true === is_array($names)) {
            // recursively remove all the sourceName.
            foreach ($names as $subName) {
                $this->unset($subName);
            }
        }

        if (false === is_string($names)) {
            throw new \TypeError('Names must be a string or an array');
        }

        $this->offsetUnset($names);
    }//end unset()


    /**
     * Return the current element
     *
     * @link   http://php.net/manual/en/iterator.current.php
     * @return callable
     */
    public function current()
    {
        $var = current($this->conditionals);
        return $var;
    }//end current()


    /**
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     */
    public function next()
    {
        next($this->conditionals);
    }//end next()


    /**
     * Return the key of the current element
     *
     * @link   http://php.net/manual/en/iterator.key.php
     * @return mixed Failure on success, or null on failure.
     */
    public function key()
    {
        $var = key($this->conditionals);
        return $var;
    }//end key()


    /**
     * Checks if current position is valid
     *
     * @link   http://php.net/manual/en/iterator.valid.php
     * @return boolean Returns true on success or false on failure.
     */
    public function valid() : bool
    {
        $key = key($this->conditionals);
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
        reset($this->conditionals);
    }//end rewind()


    /**
     * Count elements.
     *
     * @link   http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     */
    public function count() : int
    {
        $var = count($this->conditionals);
        return $var;
    }//end count()

    /**
     * Whether a offset exists
     *
     * @param mixed $offset An offset to check for.
     *
     * @return boolean true on success or false on failure.
     *
     * @throws \TypeError If offset is not an integer.
     */
    public function offsetExists($offset) : bool
    {
        if (false === is_string($offset)) {
            throw new \TypeError('Offset must be a string');
        }

        $offset = $this->standardiseString($offset);
        if (true === isset($this->conditionals[$offset])) {
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
     * @return callable|null Callable if found, null if not.
     * @throws \TypeError If offset is not an integer.
     */
    public function offsetGet($offset)
    {
        if (false === is_string($offset)) {
            throw new \TypeError('Offset must be a string');
        }

        $offset = $this->standardiseString($offset);
        if (true === isset($this->conditionals[$offset])) {
            return $this->conditionals[$offset];
        } else {
            return null;
        }
    }//end offsetGet()


    /**
     * Offset to set
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     *
     * @return void
     * @throws \TypeError If offset is not an string or value is not callable.
     * @throws \BadMethodCallException If conditional name is replicated.
     */
    public function offsetSet($offset, $value)
    {
        if (false === is_string($offset)) {
            $error = new \TypeError('Offset must be a string');
            throw $error;
        }

        $sourceName = $this->standardiseString($offset);
        if (true === isset($this->conditionals[$sourceName])) {
            throw new \BadMethodCallException('Duplicated conditional name '.$sourceName);
        }

        if (false === is_callable($value)) {
            throw new \TypeError('Conditional must be a callable for '.$sourceName.': got '.gettype($value));
        }

        $this->conditionals[$sourceName] = $value;
    }//end offsetSet()


    /**
     * Offset to unset
     *
     * @param mixed $offset The offset to unset.
     *
     * @return void
     *
     * @throws \TypeError If offset is not an integer.
     */
    public function offsetUnset($offset)
    {
        if (false === is_string($offset)) {
            throw new \TypeError('Offset must be a string');
        }

        $offset = $this->standardiseString($offset);
        unset($this->conditionals[$offset]);
    }//end offsetUnset()
}//end class
