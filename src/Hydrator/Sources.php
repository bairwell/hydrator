<?php
/**
 * Hydrator Sources.
 *
 * Project homepage: https://github.com/bairwell/hydrator
 * (c) Richard Bairwell <richard@bairwell.com> of Bairwell Ltd http://bairwell.com
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator;

/**
 * Class Sources.
 * Used to hold a collection of hydration sources.
 */
class Sources implements \Iterator, \Countable, \ArrayAccess
{

    use StandardiseString;

    /**
     * Our collection of sources.
     *
     * @var array $sources
     */
    protected $sources = [];

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
        $this->sources = [];
        $this->position = 0;
    }//end __construct()


    /**
     * Add one or more sources.
     *
     * @param array|string $sourceName Name of the source(s) to add
     * @param callable|array $source     Actual source.
     *
     * @return self
     * @throws \TypeError If source is not valid.
     * @throws \BadMethodCallException If source name already exists.
     */
    public function add($sourceName, $source) : self
    {
        if (true === is_array($sourceName)) {
            // recursively add all the source names.
            foreach ($sourceName as $subName) {
                $this->add($subName, $source);
            }

            return $this;
        }
        if (false === is_string($sourceName)) {
            throw new \TypeError('SourceName must be a string or an array');
        }
        $this->offsetSet($sourceName,$source);
        return $this;
    }//end add()


    /**
     * Removes one or more hydration sources.
     *
     * @param string|array $sourceName Name(s) of the hydration source(s) to remove.
     *
     * @throws \TypeError If sourceName is not an array or string.
     */
    public function unset($sourceName)
    {
        $removed = 0;
        if (true === is_array($sourceName)) {
            // recursively remove all the sourceName.
            foreach ($sourceName as $subName) {
                $this->unset($subName);
            }
        }

        if (false === is_string($sourceName)) {
            throw new \TypeError('SourceName must be a string or an array');
        }

        $this->offsetUnset($sourceName);
    }

    /**
     * Return the current element
     *
     * @link   http://php.net/manual/en/iterator.current.php
     * @return array|callable
     */
    public function current()
    {
        $var = current($this->sources);
        return $var;
    }//end current()


    /**
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     */
    public function next()
    {
        next($this->sources);
    }//end next()


    /**
     * Return the key of the current element
     *
     * @link   http://php.net/manual/en/iterator.key.php
     * @return mixed Failure on success, or null on failure.
     */
    public function key()
    {
        $var = key($this->sources);
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
        $key = key($this->sources);
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
        reset($this->sources);
    }//end rewind()


    /**
     * Count elements.
     *
     * @link   http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     */
    public function count() : int
    {
        $var = count($this->sources);
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
        $offset=$this->standardiseString($offset);
        if (true === isset($this->sources[$offset])) {
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
     * @return array|callable|null Array or callable if found, null if not.
     * @throws \TypeError If offset is not an integer.
     */
    public function offsetGet($offset)
    {
        if (false === is_string($offset)) {
            throw new \TypeError('Offset must be a string');
        }
        $offset=$this->standardiseString($offset);
        if (true === isset($this->sources[$offset])) {
            return $this->sources[$offset];
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
     * @throws \TypeError If offset is not an string or value is not array or callable.
     */
    public function offsetSet($offset, $value)
    {
        if (false === is_string($offset)) {
            $error = new \TypeError('Offset must be a string');
            throw $error;
        }
        $sourceName = $this->standardiseString($offset);
        if (true === isset($this->sources[$sourceName])) {
            throw new \BadMethodCallException('Duplicated source name '.$sourceName);
        }
        if (false === is_array($value) && false === is_callable($value)) {
            throw new \TypeError('Source must be an array or callable for '.$sourceName.': got '.gettype($value));
        }

        $this->sources[$sourceName] = $value;
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
        unset($this->sources[$offset]);
    }//end offsetUnset()


}
