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

use Bairwell\Hydrator\Annotations\From;
use Bairwell\Hydrator\Annotations\AsBase;
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
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Bairwell\Hydrator\StandardiseString;
use Bairwell\Hydrator\Sources;
use Bairwell\Hydrator\Conditionals;

/**
 * Class Hydrator.
 * Used to hydrate objects from a variety of sources using Doctrine annotations.
 */
class Hydrator implements LoggerAwareInterface
{

    use StandardiseString;

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
     * List of available array styles and the delimiters.
     *
     * @var array
     */
    private $arrayStyles = ['csv' => ',','ssv' => ' ','tsb' => "\n",'pipes' => '|','semi' => ';','colon' => ':'];

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
     * Sets the logger.
     *
     * @param LoggerInterface $logger Logger to set.
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }//end setLogger()


    /**
     * Main hydration system.
     *
     * @param object       $object       Object to hydrate (returned by reference).
     * @param Sources      $sources      List of sources.
     * @param Conditionals $conditionals List of conditionals.
     * @param FailureList  $failureList  List of failures.
     *
     * @return FailureList Failures.
     * @throws \TypeError If a non-object has been passed.
     */
    public function hydrateObject(
        &$object,
        Sources $sources,
        Conditionals $conditionals = null,
        FailureList $failureList = null
    )
    {
        if (false === is_object($object)) {
            throw new \TypeError('Hydrate must be passed an object for hydration');
        }

        if (null !== $this->logger) {
            $this->logger->debug(
                'Hydrator: Attempting to hydrate {className}',
                ['className' => get_class($object)]
            );
        }

        if (null === $failureList) {
            $failureList = new FailureList();
        }

        if (null === $conditionals) {
            $conditionals = new Conditionals();
        }

        $cachedClass = $this->getCachedClassForObject($object, $sources, $conditionals);

        if (null !== $this->logger) {
            $this->logger->debug(
                'Hydrator: {className} has {number} hydratable properties',
                ['className' => get_class($object),'number' => count($cachedClass)]
            );
        }

        // there may be multiple configurations for a single property.
        foreach ($cachedClass as $properties) {
            /* @var CachedProperty $property */
            foreach ($properties as $property) {
                $this->hydrateSingleProperty($property, $object, $sources, $conditionals, $failureList);
            }
        }

        return $failureList;
    }//end hydrateObject()

    /**
     * Get a "CachedClass" object for a specified object - reading from our cache if possible, if not
     * we'll build the data.
     *
     * @param object       $object       The object we want the Cached Class for.
     * @param Sources      $sources      The sources.
     * @param Conditionals $conditionals The conditionals.
     *
     * @return CachedClass
     * @throws \TypeError If we are called with a non-object.
     */
    protected function getCachedClassForObject($object, Sources $sources, Conditionals $conditionals) : CachedClass
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
            // we want all possible properties.
            $properties = $reflectedClass->getProperties();
            /* @var \ReflectionProperty $property */
            foreach ($properties as $property) {
                $cachedClass = $this->parseProperty($property, $cachedClass, $sources, $conditionals);
            }

            // only save it to the cache if it is NOT anonymous.
            if (false === strpos($className, '@')) {
                $this->saveToCache($className, $cachedClass);
            }
        }

        return $cachedClass;
    }//end getCachedClassForObject()

    /**
     * Hydrate a single property.
     *
     * @param CachedProperty $property     The property we are hydrating.
     * @param object         $object       The object we are injecting into (reference).
     * @param Sources        $sources      The hydration sources.
     * @param Conditionals   $conditionals The conditions to run.
     * @param FailureList    $failureList  Our current list of failure reasons (reference).
     *
     * @return object The object after we have injected into it.
     * @throws \TypeError If not passed an object.
     */
    public function hydrateSingleProperty(
        CachedProperty $property,
        $object,
        Sources $sources,
        Conditionals $conditionals,
        FailureList &$failureList
    )
    {
        if (false === is_object($object)) {
            throw new \TypeError(
                'HydrateSingleProperty must be passed an '.
                'object as $object: got '.gettype($object)
            );
        }

        $propertyName = $property->getName();
        $className    = $property->getClassName();
        // double check our data just in case things have changed from the cached version.
        $from                 = $property->getFrom();
        $annotationSources    = $this->validateSources(
            $from->sources,
            $className.'::$'.$propertyName,
            $sources
        );
        $annotationConditions = $this->validateConditions(
            $from->conditions,
            $className.'::$'.$propertyName,
            $conditionals
        );
        $fromField            = $from->field;
        if (true === empty($fromField)) {
            $fromField = $propertyName;
        }

        // now to check the conditions are okay for hydration.
        foreach ($annotationConditions as $annotationCondition) {
            if (false === call_user_func($conditionals[$annotationCondition])) {
                return $object;
            }
        }

        $currentValue = null;
        foreach ($annotationSources as $annotationSource) {
            if (null!==$this->logger) {
                $this->logger->debug('Attempting to hydrate {className}.{propertyName} from {annotationSource}.{fromField} : Current value "{currentValue}"',
                    ['className'=>$className,'propertyName'=>$propertyName,'annotationSource'=>$annotationSource,'fromField'=>$fromField,'currentValue'=>(string)$currentValue]);
            }
            $currentValue = $this->hydrateSinglePropertyViaSource(
                $currentValue,
                $sources[$annotationSource],
                $annotationSource,
                $fromField,
                $property,
                $failureList
            );
        }//end foreach
        if (null !== $currentValue) {
            $this->logger->debug('Setting value of {className}.{propertyName} : {type} {value}',['className'=>$className,'propertyName'=>$propertyName,'value'=>(string)$currentValue,'type'=>gettype($currentValue)]);
            // only use reflection if necessary.
            if (true === $property->isPublic()) {
                $object->$propertyName = $currentValue;
            } else {
                $reflectedProperty = new \ReflectionProperty(get_class($object), $propertyName);
                $reflectedProperty->setAccessible(true);
                $reflectedProperty->setValue($object, $currentValue);
            }
        } else {
            $this->logger->debug('No change in value for {className}.{propertyName}',['className'=>$className,'propertyName'=>$propertyName]);
        }//end if

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
     * Parse a property.
     *
     * @param \ReflectionProperty $property     The reflected property.
     * @param CachedClass         $cachedClass  Object we are building which contains all the data about this class.
     * @param Sources             $sources      Configured sources.
     * @param Conditionals        $conditionals Configured conditionals.
     *
     * @return CachedClass
     *
     * @throws AnnotationException If there is something wrong with the annotations.
     */
    protected function parseProperty(
        \ReflectionProperty $property,
        CachedClass $cachedClass,
        Sources $sources,
        Conditionals $conditionals
    ) : CachedClass
    {
        $propertyName       = $property->getName();
        $declaringClassName = $property->getDeclaringClass()->getName();
        $modifiers          = $property->getModifiers();
        $castAs             = null;
        $froms              = [];
        $annotations        = $this->getAnnotationReader()->getPropertyAnnotations($property);


        foreach ($annotations as $anno) {
            if (true === ($anno instanceof AsBase)) {
                if (null !== $castAs) {
                    throw new AnnotationException(
                        'A property can only have zero or one Cast options - '.$declaringClassName.'::$'.
                        $propertyName.' has multiple'
                    );
                }

                // @todo add validation of cast (especially important for arrays with nested casts)
                $castAs = $anno;
            } elseif (true === ($anno instanceof From)) {


                /* @var From $anno */
                $anno->sources     = $this->validateSources(
                    $anno->sources,
                    $declaringClassName.'::$'.$propertyName,
                    $sources
                );
                $anno->conditions  = $this->validateConditions(
                    $anno->conditions,
                    $declaringClassName.'::$'.$propertyName,
                    $conditionals
                );
                $anno->arrayStyles = $this->validateArrayStyles(
                    $anno->arrayStyles,
                    $declaringClassName.'::$'.$propertyName
                );
                if (true === empty($anno->field)) {
                    $anno->field = $propertyName;
                }

                $froms[] = $anno;
            }//end if
        }//end foreach

        if (false === empty($froms)) {
            /* @var From $from */
            foreach ($froms as $from) {
                $newProperty = new CachedProperty($declaringClassName, $propertyName, $from, $modifiers, $castAs);
                $cachedClass->add($newProperty);
            }
        }

        return $cachedClass;
    }//end parseProperty()

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
     * Validate the sources of a hydrate from.
     *
     * @param array   $annotationSources List of annotation sources.
     * @param string  $propertyFullName  The name of the property we are checking.
     * @param Sources $sources           Actual sources.
     *
     * @return array
     * @throws AnnotationException If there is something wrong with the annotations.
     */
    protected function validateSources(array $annotationSources, string $propertyFullName, Sources $sources)
    {
        if (true === empty($sources)) {
            throw new AnnotationException('No source specified in annotation for '.$propertyFullName);
        }

        $returnSources = [];
        foreach ($annotationSources as $annotationSource) {
            if (false === is_string($annotationSource)) {
                throw new AnnotationException(
                    'Annotation sources must be an array of strings for '.
                    $propertyFullName.': encountered '.
                    gettype($annotationSource)
                );
            }

            if (false === isset($sources[$annotationSource])) {
                throw new AnnotationException(
                    'Missing/unrecognised source "'.
                    $annotationSource.'" in '.
                    $propertyFullName
                );
            } else {
                $returnSources[] = $annotationSource;
            }
        }

        return $returnSources;
    }//end validateSources()

    /**
     * Validate the array styles of a hydrate from.
     *
     * @param array  $arrayStyles      List of annotation array styles.
     * @param string $propertyFullName The name of the property we are checking.
     *
     * @return array
     * @throws AnnotationException If there is something wrong with the annotations.
     */
    protected function validateArrayStyles(array $arrayStyles, string $propertyFullName) : array
    {
        $returnStyles = [];
        $arrayStyles  = array_unique($arrayStyles);
        foreach ($arrayStyles as $style) {
            $style = strtolower($style);
            if ($style !== 'basic' && false === isset($this->arrayStyles[$style])) {
                throw new AnnotationException(
                    'Unrecognised array style of '.
                    $style.
                    ' when processing '.$propertyFullName
                );
            }

            $returnStyles[] = $style;
        }

        return $returnStyles;
    }//end validateArrayStyles()

    /**
     * Validate the conditions of a hydrate from.
     *
     * @param string[]     $conditions       Array of conditions.
     * @param string       $propertyFullName The name of the property we are checking.
     * @param Conditionals $conditionals     The conditionals.
     *
     * @return string[]
     * @throws AnnotationException If there is something wrong with the annotations.
     */
    protected function validateConditions(
        array $conditions,
        string $propertyFullName,
        Conditionals $conditionals
    ) : array
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

            if (false === isset($conditionals[$condition])) {
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
     * Hydrate a single property via single source.
     *
     * @param mixed          $currentValue Current value of the property.
     * @param array|callable $source       The source we are using.
     * @param string         $sourceName   Name of the source.
     * @param string         $fromField    Which field should we be reading from.
     * @param CachedProperty $property     The property we are working on.
     * @param FailureList    $failureList  Referenced list of failures.
     *
     * @return mixed The new value.
     * @throws \TypeError If source is not callable or array.
     */
    private function hydrateSinglePropertyViaSource(
        $currentValue,
        $source,
        string $sourceName,
        string $fromField,
        CachedProperty $property,
        FailureList &$failureList
    )
    {
        if (true === is_array($source)) {
            $data = null;
            if (true === array_key_exists($fromField, $source)) {
                $data = $source[$fromField];
            }
        } elseif (true === is_callable($source)) {
            $data = call_user_func($source, $fromField);
        } else {
            throw new \TypeError('Source must be an array or callable: got '.gettype($source));
        }

        $isValid=true;
        if (null===$data || (true===is_array($data) && 0===count($data))) {
            $isValid=false;
        }
        if (true===$isValid) {
            $arrayStyles = $property->getFrom()->arrayStyles;
            if (false === empty($arrayStyles)) {
                $data = $this->extractFromArray($data, $arrayStyles);
            }

            if (false === $property->hasCastAs()) {
                $currentValue = $data;
                if (null !== $this->logger) {
                    $this->logger->debug(
                        'Hydrator: No cast setting for field {fromField}: {currentValue}',
                        ['fromField' => $fromField,
                            'currentValue' => $currentValue]
                    );
                }
            } else {
                $castAs   = $property->getCastAs();
                $newValue = $castAs->cast($data);
                if (true === $castAs->hasErrored()) {
                    $failure = new Failure();
                    $failure->setInputField($fromField)
                        ->setInputValue($data)
                        ->setMessage($castAs->getErrorMessage())
                        ->setTokens($castAs->getErrorTokens())
                        ->setSource($sourceName);
                    $failureList->add($failure);
                    if (null !== $this->logger) {
                        $this->logger->debug(
                            'Hydrator: Cast failed for field {fromField}: {currentValue}: {castErrorMessage}',
                            ['fromField' => $fromField,
                                'currentValue' => $currentValue,'castErrorMessage' => $castAs->getErrorMessage()]
                        );
                    }
                } else {
                    $currentValue = $newValue;
                }
            }//end if
        }//end if

        return $currentValue;
    }//end hydrateSinglePropertyViaSource()

    /**
     * Try to extract array data from input data.
     *
     * @param mixed $data        Input data.
     * @param array $arrayStyles Which array styles are supported by this property.
     *
     * @return mixed Either input data or expanded array data.
     */
    protected function extractFromArray($data, array $arrayStyles = [])
    {
        foreach ($arrayStyles as $style) {
            if ('basic' === $style && true === is_array($data)) {
                return $data;
            } elseif (true === isset($this->arrayStyles[$style])) {
                $extracted = str_getcsv($data, $this->arrayStyles[$style]);
                if (count($extracted) > 0) {
                    return $extracted;
                }
            }
        }

        return $data;
    }//end extractFromArray()


    /**
     * Get the annotation reader.
     *
     * @return Reader
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
}//end class
