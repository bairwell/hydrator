<?php
/**
 * Hydrator.
 * Used to hydrate objects from a variety of sources using Doctrine annotations.
 * Project homepage: https://github.com/bairwell/hydrator
 * (c) Richard Bairwell <richard@bairwell.com> of Bairwell Ltd http://bairwell.com
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types = 1);

namespace Bairwell;

use Bairwell\Hydrator\Annotations\HydrateFrom;
use Bairwell\Hydrator\Annotations\TypeCast\CastBase;
use Bairwell\Hydrator\CachedClass;
use Bairwell\Hydrator\CachedProperty;
use Bairwell\Hydrator\Failure;
use Bairwell\Hydrator\FailureList;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ArrayCache;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Hydrator.
 * Used to hydrate objects from a variety of sources using Doctrine annotations.
 */
class Hydrator
{

    /**
     * An callable sources with the source name as the key.
     *
     * @var array $sources
     */
    protected $sources = [];
    /**
     * An array of callables which can be used as conditional checks.
     *
     * @var array $conditions
     */
    protected $conditionals = [];
    /**
     * The annotation reader system.
     *
     * @var AnnotationReader $annotationReader
     */
    protected $annotationReader = null;

    /**
     * The logger.
     *
     * @var LoggerInterface $logger
     */
    protected $logger = null;

    /**
     * Cache system.
     *
     * @var CacheItemPoolInterface
     */
    protected $cachePool = null;

    /**
     * Our cache key prefix.
     *
     * @var string
     */
    protected $cacheKeyPrefix = null;

    /**
     * Maximum length of time to cache items.
     *
     * @var integer
     */
    protected $cacheExpiresAfter = 3600;

    /**
     * Hydrator constructor.
     *
     * @param LoggerInterface|null        $logger            The logger.
     * @param Reader|null                 $annotationReader  A Doctrine compatible Annotation reader.
     * @param CacheItemPoolInterface|null $cachePool         A PSR compliant caching system.
     * @param string|null                 $cacheKeyPrefix    Our prefix for the cache entries.
     * @param integer                     $cacheExpiresAfter Our time length of cache entries.
     */
    public function __construct(
        LoggerInterface $logger = null,
        Reader $annotationReader = null,
        CacheItemPoolInterface $cachePool = null,
        string $cacheKeyPrefix = null,
        int $cacheExpiresAfter = 3600
    )
    {
        $this->logger           = $logger;
        $this->annotationReader = $annotationReader;
        $this->cachePool        = $cachePool;
        if (null === $cacheKeyPrefix) {
            $cacheKeyPrefix = __CLASS__;
        }

        $this->cacheKeyPrefix    = $cacheKeyPrefix;
        $this->cacheExpiresAfter = $cacheExpiresAfter;
    }//end __construct()


    /**
     * Add a hydration source.
     *
     * @param string|array $sourceName Name(s) of the source(s) to add.
     * @param callable     $source     Actual source.
     *
     * @return Hydrator
     * @throws \TypeError If source is not valid.
     * @throws \BadMethodCallException If source name already exists.
     */
    public function addHydrationSource($sourceName, callable $source) : self
    {
        if (true === is_array($sourceName)) {
            // recursively add all the source names.
            foreach ($sourceName as $subName) {
                $this->addHydrationSource($subName, $source);
            }

            return $this;
        }

        if (false === is_string($sourceName)) {
            throw new \TypeError('SourceName must be a string or an array');
        }

        $sourceName = $this->standardiseString($sourceName);
        if (true === isset($this->sources[$sourceName])) {
            throw new \BadMethodCallException('Duplicated source name '.$sourceName);
        }

        $this->sources[$sourceName] = $source;

        return $this;
    }//end addHydrationSource()


    /**
     * Removes all hydration sources.
     *
     * @return int Number of hydration sources removed.
     */
    public function unsetAllHydrationSources() : int
    {
        $removed       = count($this->sources);
        $this->sources = [];

        return $removed;
    }//end unsetAllHydrationSources()


    /**
     * Removes one or more hydration sources.
     *
     * @param string|array $sourceName Name(s) of the hydration source(s) to remove.
     *
     * @return int Number of hydration sources removed.
     * @throws \TypeError If sourceName is not an array or string.
     */
    public function unsetHydrationSource($sourceName) : int
    {
        $removed = 0;
        if (true === is_array($sourceName)) {
            // recursively remove all the sourceName.
            foreach ($sourceName as $subName) {
                $removed += $this->unsetHydrationSource($subName);
            }

            return $removed;
        }

        if (false === is_string($sourceName)) {
            throw new \TypeError('SourceName must be a string or an array');
        }

        $sourceName = $this->standardiseString($sourceName);
        if (false === isset($this->sources[$sourceName])) {
            return $removed;
        }

        unset($this->sources[$sourceName]);
        $removed ++;

        return $removed;
    }//end unsetHydrationSource()


    /**
     * Add a conditional.
     *
     * @param string|array $name        Name(s) of the conditionals(s) to add.
     * @param callable     $conditional Actual conditional.
     *
     * @return Hydrator
     * @throws \TypeError If conditional is not valid.
     * @throws \BadMethodCallException If name already exists.
     */
    public function addConditional($name, callable $conditional) : self
    {
        if (true === is_array($name)) {
            // recursively add all the conditional names.
            foreach ($name as $subName) {
                $this->addConditional($subName, $conditional);
            }

            return $this;
        }

        if (false === is_string($name)) {
            throw new \TypeError('Name must be a string or an array');
        }

        $name = $this->standardiseString($name);
        if (true === isset($this->conditionals[$name])) {
            throw new \BadMethodCallException('Duplicated conditional name '.$name);
        }

        $this->conditionals[$name] = $conditional;

        return $this;
    }//end addConditional()


    /**
     * Removes all conditionals.
     *
     * @return int Number of conditionals removed.
     */
    public function unsetAllConditionals() : int
    {
        $removed            = count($this->conditionals);
        $this->conditionals = [];

        return $removed;
    }//end unsetAllConditionals()


    /**
     * Removes one or more conditionals.
     *
     * @param string|array $name Name(s) of the conditionals(s) to remove.
     *
     * @return int Number of conditionals removed.
     * @throws \TypeError If name is not an array or string.
     */
    public function unsetConditional($name) : int
    {
        $removed = 0;
        if (true === is_array($name)) {
            // recursively remove all the sourceName.
            foreach ($name as $subName) {
                $removed += $this->unsetConditional($subName);
            }

            return $removed;
        }

        if (false === is_string($name)) {
            throw new \TypeError('Name must be a string or an array');
        }

        $name = $this->standardiseString($name);
        if (false === isset($this->conditionals[$name])) {
            return $removed;
        }

        unset($this->conditionals[$name]);

        $removed ++;

        return $removed;
    }//end unsetConditional()


    /**
     * Main hydration system.
     *
     * @param object      $object      Object to hydrate (returned by reference).
     * @param FailureList $failureList List of failures.
     *
     * @return FailureList Failures.
     * @throws \TypeError If a non-object has been passed.
     */
    public function hydrateObject(&$object, FailureList $failureList = null)
    {
        if (false === is_object($object)) {
            throw new \TypeError('Hydrate must be passed an object for hydration');
        }

        if (null === $failureList) {
            $failureList = new FailureList();
        }

        $cachedClass = $this->getCachedClassForObject($object);

        // there may be multiple configurations for a single property.
        foreach ($cachedClass as $properties) {
            /* @var CachedProperty $property */
            foreach ($properties as $property) {
                $this->hydrateSingleProperty($property, $object, $failureList);
            }
        }

        return $failureList;
    }//end hydrateObject()

    /**
     * Hydrate a single property.
     *
     * @param CachedProperty $property    The property we are hydrating.
     * @param object         $object      The object we are injecting into (reference).
     * @param FailureList    $failureList Our current list of failure reasons (reference).
     *
     * @return object The object after we have injected into it.
     * @throws \TypeError If not passed an object.
     * @throws \Exception If sources/conditionals are inaccurate.
     */
    public function hydrateSingleProperty(
        CachedProperty $property,
        $object,
        FailureList &$failureList
    )
    {
        if (false === is_object($object)) {
            throw new \TypeError('HydrateSingleProperty must be passed an object as $object: got '.gettype($object));
        }

        $propertyName = $property->getName();
        $className    = $property->getClassName();
        // double check our data just in case things have changed from the cached version.
        $from       = $property->getFrom();
        $sources    = $this->validateSources($from->sources, $className.'::$'.$propertyName);
        $conditions = $this->validateConditions($from->conditions, $className.'::$'.$propertyName);
        $fromField  = $from->field;
        if (true === empty($fromField)) {
            $fromField = $propertyName;
        }

        // now to check the conditions are okay for hydration.
        foreach ($conditions as $condition) {
            if (false === is_callable($this->conditionals[$condition])) {
                throw new \Exception(
                    'Conditional "'.$condition.'" is not callable when checking '.$className.'::$'.$propertyName
                );
            }

            // they aren't, so let's just return.
            if (false === call_user_func($this->conditionals[$condition])) {
                return $object;
            }
        }

        $currentValue = null;
        foreach ($sources as $source) {
            if (false === is_callable($this->sources[$source])) {
                throw new \Exception(
                    'Source "'.$source.'" is not callable when hydrating '.$className.'::$'.$propertyName
                );
            }

            $data = call_user_func($this->sources[$source], $fromField);
            if (null !== $data) {
                if (false === $property->hasCastAs()) {
                    $currentValue = $data;
                } else {
                    $castAs   = $property->getCastAs();
                    $newValue = $castAs->cast($data);
                    if (true === $castAs->hasErrored()) {
                        $failure = new Failure();
                        $failure->setInputField($fromField)
                            ->setInputValue($data)
                            ->setMessage($castAs->getErrorMessage())
                            ->setTokens($castAs->getErrorTokens())
                            ->setSource($source);
                        $failureList->add($failure);
                    } else {
                        $currentValue = $newValue;
                    }
                }
            }
        }//end foreach
        if (null !== $currentValue) {
            $object->$propertyName = $currentValue;
        }

        return $object;
    }//end hydrateSingleProperty()

    /**
     * Get a cached item from the PSR cache.
     * Has protection to ensure that not only is the right sort of object
     * is returned, but also that it is the object we are looking for.
     *
     * @param string $className Name of the class.
     *
     * @return CachedClass
     */
    public function getFromCache(string $className) : CachedClass
    {
        $cacheKey = $this->cacheKeyPrefix.ucfirst($this->standardiseString($className));
        // see if we have a caching system enabled
        if (null !== $this->cachePool) {
            /* @var CacheItemInterface $cached */
            $cached = $this->cachePool->getItem($cacheKey);
            if (true === $cached->isHit()) {
                /* @var CachedClass $value */
                $value = $cached->get();
                if (true === ($value instanceof CachedClass)) {
                    if ($className === $value->getName()) {
                        return $value;
                    }
                }
            }
        }

        $value = new CachedClass('');

        return $value;
    }//end getFromCache()

    /**
     * Get the annotation reader.
     *
     * @return AnnotationReader
     * @throws \RuntimeException If we are missing doctrine.
     */
    protected function getAnnotationReader() : Reader
    {
        if (null === $this->annotationReader) {
            if (false === class_exists('Doctrine\Common\Annotations\AnnotationReader')
                || false === class_exists('Doctrine\Common\Cache\ArrayCache')
            ) {
                throw new \RuntimeException(
                    __CLASS__.' requires the packages doctrine/annotations and doctrine/cache to be installed.'
                );
            }

            AnnotationRegistry::registerLoader('class_exists');

            $cache                  = new ArrayCache();
            $reader                 = new AnnotationReader();
            $this->annotationReader = new CachedReader($reader, $cache);
        }

        return $this->annotationReader;
    }//end getAnnotationReader()

    /**
     * Save an item to the PSR cache.
     *
     * @param string      $className   Name of the class.
     * @param CachedClass $cachedClass The class we are saving.
     *
     * @return boolean True if saved to cache, false otherwise.
     */
    protected function saveToCache(string $className, CachedClass $cachedClass) : bool
    {
        if (null === $this->cachePool) {
            return false;
        }

        $cacheKey = $this->cacheKeyPrefix.ucfirst($this->standardiseString($className));
        /* @var CacheItemInterface $cached */
        $cached = $this->cachePool->getItem($cacheKey);
        $cached->set($cachedClass);
        $cached->expiresAfter($this->cacheExpiresAfter);
        $this->cachePool->save($cached);

        return true;
    }//end saveToCache()


    /**
     * Get a "CachedClass" object for a specified object - reading from our cache if possible, if not
     * we'll build the data.
     *
     * @param object $object The object we want the Cached Class for.
     *
     * @return CachedClass
     * @throws \TypeError If we are called with a non-object.
     */
    protected function getCachedClassForObject($object) : CachedClass
    {
        if (false === is_object($object)) {
            throw new \TypeError('getCachedClassForObject can only be called with objects: got '.gettype($object));
        }

        $className   = get_class($object);
        $cachedClass = null;
        // only get from the cache if it is not anonymous. Anonymous
        // classes have an @ in the name according to https://wiki.php.net/rfc/anonymous_classes
        if (false === strpos($className, '@')) {
            $cachedClass = $this->getFromCache($className);
        }

        // we don't seem to have it cached.
        if (null === $cachedClass || '' === $cachedClass->getName()) {
            $cachedClass    = new CachedClass($className);
            $reflectedClass = new \ReflectionClass($className);

            $properties = $reflectedClass->getProperties(\ReflectionProperty::IS_PUBLIC);
            /* @var \ReflectionProperty $property */
            foreach ($properties as $property) {
                $cachedClass = $this->parseProperty($property, $cachedClass);
            }

            // only save it to the cache if it is NOT anonymous.
            if (false === strpos($className, '@')) {
                $this->saveToCache($className, $cachedClass);
            }
        }

        return $cachedClass;
    }//end getCachedClassForObject()


    /**
     * Parse a property.
     *
     * @param \ReflectionProperty $property    The reflected property.
     * @param CachedClass         $cachedClass The object we are building which contains all the data about this class.
     *
     * @return CachedClass
     * @throws AnnotationException If there is something wrong with the annotations.
     */
    protected function parseProperty(
        \ReflectionProperty $property,
        CachedClass $cachedClass
    ) : CachedClass
    {
        $propertyName       = $property->getName();
        $declaringClassName = $property->getDeclaringClass()->getName();
        $castAs             = null;
        $froms              = [];
        $annotations        = $this->getAnnotationReader()
            ->getPropertyAnnotations($property);
        foreach ($annotations as $anno) {
            if (true === ($anno instanceof CastBase)) {
                if (null !== $castAs) {
                    throw new AnnotationException(
                        'A property can only have zero or one Cast options - '.$declaringClassName.'::$'.
                        $propertyName.' has multiple'
                    );
                }

                $castAs = $anno;
            } elseif (true === ($anno instanceof HydrateFrom)) {
                /* @var HydrateFrom $anno */
                $anno->sources    = $this->validateSources(
                    $anno->sources,
                    $declaringClassName.'::$'.$propertyName
                );
                $anno->conditions = $this->validateConditions(
                    $anno->conditions,
                    $declaringClassName.'::$'.$propertyName
                );
                if (true === empty($anno->field)) {
                    $anno->field = $propertyName;
                }

                $froms[] = $anno;
            }//end if
        }//end foreach

        if (false === empty($froms)) {
            /* @var HydrateFrom $from */
            foreach ($froms as $from) {
                $newProperty = new CachedProperty($declaringClassName, $propertyName, $from, $castAs);
                $cachedClass->add($newProperty);
            }
        }

        return $cachedClass;
    }//end parseProperty()


    /**
     * Validate the conditions of a hydrate from.
     *
     * @param string[] $conditions       Array of conditions.
     * @param string   $propertyFullName The name of the property we are checking.
     *
     * @return string[]
     * @throws AnnotationException If there is something wrong with the annotations.
     */
    protected function validateConditions(array $conditions, string $propertyFullName) : array
    {
        if (true === empty($conditions)) {
            return [];
        }

        $returnConditions = [];
        foreach ($conditions as $condition) {
            if (false === is_string($condition)) {
                throw new AnnotationException(
                    'Conditions must be an array of strings for '.
                    $propertyFullName.
                    ': encountered '.gettype($condition)
                );
            }

            $condition = $this->standardiseString($condition);
            if (false === isset($this->conditionals[$condition])) {
                throw new AnnotationException(
                    'Missing/unrecognised conditional "'.$condition.'" in '.$propertyFullName
                );
            } else {
                $returnConditions[] = $condition;
            }
        }

        return $returnConditions;
    }//end validateConditions()


    /**
     * Validate the sources of a hydrate from.
     *
     * @param array  $sources          List of sources.
     * @param string $propertyFullName The name of the property we are checking.
     *
     * @return array
     * @throws AnnotationException If there is something wrong with the annotations.
     */
    protected function validateSources(array $sources, string $propertyFullName)
    {
        if (true === empty($sources)) {
            throw new AnnotationException('No source specified in annotation for '.$propertyFullName);
        }

        $returnSources = [];
        foreach ($sources as $source) {
            if (false === is_string($source)) {
                throw new AnnotationException(
                    'Sources must be an array of strings for '.
                    $propertyFullName.': encountered '.
                    gettype($source)
                );
            }

            $source = $this->standardiseString($source);
            if (false === isset($this->sources[$source])) {
                throw new AnnotationException('Missing/unrecognised source "'.$source.'" in '.$propertyFullName);
            } else {
                $returnSources[] = $source;
            }
        }

        return $returnSources;
    }//end validateSources()


    /**
     * Standardise a string. Convers XyZ_abc-def to xyzAbcDef.
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
    }//end standardiseString()
}//end class
