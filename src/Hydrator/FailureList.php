<?php
/**
 * A list/collection of failure reports.
 *
 * Project homepage: https://github.com/bairwell/hydrator
 * (c) Richard Bairwell <richard@bairwell.com> of Bairwell Ltd http://bairwell.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator;

/**
 * Class FailureList.
 *
 * A list/collection of failure reports.
 */
class FailureList implements \Iterator, \Countable, \ArrayAccess
{

    /**
     * Our collection of failures.
     *
     * @var Failure[] $failures
     */
    protected $failures = [];

    /**
     * Current iterator position.
     *
     * @var integer
     */
    protected $position = 0;

    /**
     * FailureList constructor.
     */
    public function __construct()
    {
        $this->failures = [];
        $this->position = 0;
    }//end __construct()


    /**
     * Add one or more failures.
     *
     * Allows a variable-length list of arguments (variadic) - see
     * http://php.net/manual/en/functions.arguments.php#functions.variable-arg-list
     *
     * @param Failure $failure The failure reason we are adding.
     *
     * @return self
     */
    public function add(Failure $failure) : self
    {
            $this->failures[] = $failure;

        return $this;
    }//end add()


    /**
     * Return the current element
     *
     * @link   http://php.net/manual/en/iterator.current.php
     * @return Failure
     */
    public function current() : Failure
    {
        $var = current($this->failures);
        return $var;
    }//end current()


    /**
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     */
    public function next()
    {
        next($this->failures);
    }//end next()


    /**
     * Return the key of the current element
     *
     * @link   http://php.net/manual/en/iterator.key.php
     * @return mixed Failure on success, or null on failure.
     */
    public function key()
    {
        $var = key($this->failures);
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
        $key = key($this->failures);
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
        reset($this->failures);
    }//end rewind()


    /**
     * Count elements of an object
     *
     * @link   http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     */
    public function count() : int
    {
        $var = count($this->failures);
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
        if (false === is_int($offset)) {
            throw new \TypeError('Offset must be an integer');
        }

        if (true === isset($this->failures[$offset])) {
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
     * @return Failure|null Failure object if found, null if not.
     * @throws \TypeError If offset is not an integer.
     */
    public function offsetGet($offset)
    {
        if (false === is_int($offset)) {
            throw new \TypeError('Offset must be an integer');
        }

        if (true === isset($this->failures[$offset])) {
            return $this->failures[$offset];
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
     * @throws \TypeError If offset is not an integer or value is not a Failure object.
     */
    public function offsetSet($offset, $value)
    {
        if (false === is_int($offset)) {
            $error = new \TypeError('Offset must be an integer');
            throw $error;
        }

        if (false === ($value instanceof Failure)) {
            $message = 'Value must be an instance of Failure: got '.gettype($value);
            if (true === is_object($value)) {
                $message .= ' '.get_class($value);
            }

            $error = new \TypeError($message);
            throw $error;
        }

        $this->failures[$offset] = $value;
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
        if (false === is_int($offset)) {
            throw new \TypeError('Offset must be an integer');
        }

        unset($this->failures[$offset]);
    }//end offsetUnset()
}//end class
