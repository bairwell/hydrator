<?php
/**
 * Test
 */
declare (strict_types = 1);

// use a custom namespace as we want to override class_exists.
namespace Bairwell;

use Bairwell\Hydrator\Annotations\HydrateFrom;
use Bairwell\Hydrator\Annotations\TypeCast\AsString;
use Bairwell\Hydrator\CachedClass;
use Doctrine\Common\Annotations\AnnotationException;
use Bairwell\Hydrator;

if (false===function_exists('\Bairwell\class_exists')) {
    /**
     * Override class exists.
     *
     * @param string $className Class name.
     * @return bool
     */
    function class_exists(string $className) : bool {
        if (true===isset($GLOBALS['BairwellClassExistsOverride']) && true===is_callable($GLOBALS['BairwellClassExistsOverride'])) {
            return call_user_func($GLOBALS['BairwellClassExistsOverride'],$className);
        } else {
            return \class_exists($className);
        }
    }
}
/**
 * Class HydratorTest.
 */
class HydratorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Do this before each test.
     * @before
     */
    public function before() {
        unset($GLOBALS['BairwellClassExistsOverride']);
    }
    /**
     * Test constructor with nothing passed.
     *
     * @test
     * @covers \Bairwell\Hydrator::__construct
     */
    public function testConstructorNothingPassed()
    {
        $sut       = new Hydrator();
        $reflected = new \ReflectionClass($sut);
        $class     = get_class($sut);
        $this->assertNull($this->getValueFromProtected($sut, $reflected, 'logger'));
        $this->assertNull($this->getValueFromProtected($sut, $reflected, 'annotationReader'));
        $this->assertNull($this->getValueFromProtected($sut, $reflected, 'cachePool'));
        $this->assertEquals($class, $this->getValueFromProtected($sut, $reflected, 'cacheKeyPrefix'));
        $this->assertEquals(3600, $this->getValueFromProtected($sut, $reflected, 'cacheExpiresAfter'));
        $this->assertInternalType('array', $this->getValueFromProtected($sut, $reflected, 'sources'));
        $this->assertEmpty($this->getValueFromProtected($sut, $reflected, 'sources'));
        $this->assertInternalType('array', $this->getValueFromProtected($sut, $reflected, 'conditionals'));
        $this->assertEmpty($this->getValueFromProtected($sut, $reflected, 'conditionals'));
    }

    /**
     * Test constructor with logger passed.
     *
     * @test
     * @covers \Bairwell\Hydrator::__construct
     */
    public function testConstructorLogger()
    {
        $logger    = $this->getMockForAbstractClass('\Psr\Log\LoggerInterface');
        $sut       = new Hydrator($logger);
        $reflected = new \ReflectionClass($sut);
        $class     = get_class($sut);
        $this->assertSame($logger, $this->getValueFromProtected($sut, $reflected, 'logger'));
        $this->assertNull($this->getValueFromProtected($sut, $reflected, 'annotationReader'));
        $this->assertNull($this->getValueFromProtected($sut, $reflected, 'cachePool'));
        $this->assertEquals($class, $this->getValueFromProtected($sut, $reflected, 'cacheKeyPrefix'));
        $this->assertEquals(3600, $this->getValueFromProtected($sut, $reflected, 'cacheExpiresAfter'));
        $this->assertInternalType('array', $this->getValueFromProtected($sut, $reflected, 'sources'));
        $this->assertEmpty($this->getValueFromProtected($sut, $reflected, 'sources'));
        $this->assertInternalType('array', $this->getValueFromProtected($sut, $reflected, 'conditionals'));
        $this->assertEmpty($this->getValueFromProtected($sut, $reflected, 'conditionals'));
    }

    /**
     * Test constructor with annotation reader passed.
     *
     * @test
     * @covers \Bairwell\Hydrator::__construct
     */
    public function testConstructorAnnotationReader()
    {
        $annotationReader = $this->getMockForAbstractClass('\Doctrine\Common\Annotations\Reader');
        $sut              = new Hydrator(null, $annotationReader);
        $reflected        = new \ReflectionClass($sut);
        $class            = get_class($sut);
        $this->assertSame($annotationReader, $this->getValueFromProtected($sut, $reflected, 'annotationReader'));
        $this->assertNull($this->getValueFromProtected($sut, $reflected, 'logger'));
        $this->assertNull($this->getValueFromProtected($sut, $reflected, 'cachePool'));
        $this->assertEquals($class, $this->getValueFromProtected($sut, $reflected, 'cacheKeyPrefix'));
        $this->assertEquals(3600, $this->getValueFromProtected($sut, $reflected, 'cacheExpiresAfter'));
        $this->assertInternalType('array', $this->getValueFromProtected($sut, $reflected, 'sources'));
        $this->assertEmpty($this->getValueFromProtected($sut, $reflected, 'sources'));
        $this->assertInternalType('array', $this->getValueFromProtected($sut, $reflected, 'conditionals'));
        $this->assertEmpty($this->getValueFromProtected($sut, $reflected, 'conditionals'));
    }

    /**
     * Test constructor with cache item passed.
     *
     * @test
     * @covers \Bairwell\Hydrator::__construct
     */
    public function testConstructorCacheItem()
    {
        $cache     = $this->getMockForAbstractClass('\Psr\Cache\CacheItemPoolInterface');
        $sut       = new Hydrator(null, null, $cache);
        $reflected = new \ReflectionClass($sut);
        $class     = get_class($sut);
        $this->assertNull($this->getValueFromProtected($sut, $reflected, 'logger'));
        $this->assertNull($this->getValueFromProtected($sut, $reflected, 'annotationReader'));
        $this->assertSame($cache, $this->getValueFromProtected($sut, $reflected, 'cachePool'));
        $this->assertEquals($class, $this->getValueFromProtected($sut, $reflected, 'cacheKeyPrefix'));
        $this->assertEquals(3600, $this->getValueFromProtected($sut, $reflected, 'cacheExpiresAfter'));
        $this->assertInternalType('array', $this->getValueFromProtected($sut, $reflected, 'sources'));
        $this->assertEmpty($this->getValueFromProtected($sut, $reflected, 'sources'));
        $this->assertInternalType('array', $this->getValueFromProtected($sut, $reflected, 'conditionals'));
        $this->assertEmpty($this->getValueFromProtected($sut, $reflected, 'conditionals'));
    }

    /**
     * Test constructor with key prefix passed.
     *
     * @test
     * @covers \Bairwell\Hydrator::__construct
     */
    public function testConstructorKeyPrefix()
    {
        $sut       = new Hydrator(null, null, null, 'jeff');
        $reflected = new \ReflectionClass($sut);
        $class     = get_class($sut);
        $this->assertNull($this->getValueFromProtected($sut, $reflected, 'logger'));
        $this->assertNull($this->getValueFromProtected($sut, $reflected, 'annotationReader'));
        $this->assertNull($this->getValueFromProtected($sut, $reflected, 'cachePool'));
        $this->assertEquals('jeff', $this->getValueFromProtected($sut, $reflected, 'cacheKeyPrefix'));
        $this->assertEquals(3600, $this->getValueFromProtected($sut, $reflected, 'cacheExpiresAfter'));
        $this->assertInternalType('array', $this->getValueFromProtected($sut, $reflected, 'sources'));
        $this->assertEmpty($this->getValueFromProtected($sut, $reflected, 'sources'));
        $this->assertInternalType('array', $this->getValueFromProtected($sut, $reflected, 'conditionals'));
        $this->assertEmpty($this->getValueFromProtected($sut, $reflected, 'conditionals'));
    }

    /**
     * Test constructor with expires after passed.
     *
     * @test
     * @covers \Bairwell\Hydrator::__construct
     */
    public function testConstructorExpiresAfter()
    {
        $sut       = new Hydrator(null, null, null, null, 1234);
        $reflected = new \ReflectionClass($sut);
        $class     = get_class($sut);
        $this->assertNull($this->getValueFromProtected($sut, $reflected, 'logger'));
        $this->assertNull($this->getValueFromProtected($sut, $reflected, 'annotationReader'));
        $this->assertNull($this->getValueFromProtected($sut, $reflected, 'cachePool'));
        $this->assertEquals($class, $this->getValueFromProtected($sut, $reflected, 'cacheKeyPrefix'));
        $this->assertEquals(1234, $this->getValueFromProtected($sut, $reflected, 'cacheExpiresAfter'));
        $this->assertInternalType('array', $this->getValueFromProtected($sut, $reflected, 'sources'));
        $this->assertEmpty($this->getValueFromProtected($sut, $reflected, 'sources'));
        $this->assertInternalType('array', $this->getValueFromProtected($sut, $reflected, 'conditionals'));
        $this->assertEmpty($this->getValueFromProtected($sut, $reflected, 'conditionals'));
    }

    /**
     * Test constructor with eveything passed.
     *
     * @test
     * @covers \Bairwell\Hydrator::__construct
     */
    public function testConstructorAll()
    {
        $logger           = $this->getMockForAbstractClass('\Psr\Log\LoggerInterface');
        $annotationReader = $this->getMockForAbstractClass('\Doctrine\Common\Annotations\Reader');
        $cache            = $this->getMockForAbstractClass('\Psr\Cache\CacheItemPoolInterface');
        $sut              = new Hydrator($logger, $annotationReader, $cache, 'abc123', 567);
        $reflected        = new \ReflectionClass($sut);
        $class            = get_class($sut);
        $this->assertSame($logger, $this->getValueFromProtected($sut, $reflected, 'logger'));
        $this->assertSame($annotationReader, $this->getValueFromProtected($sut, $reflected, 'annotationReader'));
        $this->assertSame($cache, $this->getValueFromProtected($sut, $reflected, 'cachePool'));
        $this->assertEquals('abc123', $this->getValueFromProtected($sut, $reflected, 'cacheKeyPrefix'));
        $this->assertEquals(567, $this->getValueFromProtected($sut, $reflected, 'cacheExpiresAfter'));
        $this->assertInternalType('array', $this->getValueFromProtected($sut, $reflected, 'sources'));
        $this->assertEmpty($this->getValueFromProtected($sut, $reflected, 'sources'));
        $this->assertInternalType('array', $this->getValueFromProtected($sut, $reflected, 'conditionals'));
        $this->assertEmpty($this->getValueFromProtected($sut, $reflected, 'conditionals'));
    }

    /**
     * Test hydration sources.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @covers \Bairwell\Hydrator::addHydrationSource
     * @covers \Bairwell\Hydrator::unsetHydrationSource
     * @covers \Bairwell\Hydrator::unsetAllHydrationSources
     */
    public function testHydrationSources()
    {
        $sut       = new Hydrator();
        $reflected = new \ReflectionClass($sut);
        $this->assertInternalType('array', $this->getValueFromProtected($sut, $reflected, 'sources'));
        $this->assertEmpty($this->getValueFromProtected($sut, $reflected, 'sources'));
        $callable  = function () {
        };
        $callable2 = function () {
        };
        $this->assertSame($sut, $sut->addHydrationSource('first', $callable), 'Should be okay');
        $sources = $this->getValueFromProtected($sut, $reflected, 'sources');
        $this->assertInternalType('array', $sources);
        $this->assertCount(1, $sources);
        $this->assertArrayHasKey('first', $sources);
        $this->assertSame($callable, $sources['first']);
        $this->assertSame($sut, $sut->addHydrationSource(['second', 'third'], $callable2), 'Should be okay');
        $sources = $this->getValueFromProtected($sut, $reflected, 'sources');
        $this->assertInternalType('array', $sources);
        $this->assertCount(3, $sources);
        $this->assertArrayHasKey('first', $sources);
        $this->assertSame($callable, $sources['first']);
        $this->assertArrayHasKey('second', $sources);
        $this->assertSame($callable2, $sources['second']);
        $this->assertArrayHasKey('third', $sources);
        $this->assertSame($callable2, $sources['third']);
        try {
            $sut->addHydrationSource(123, $callable);
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('SourceName must be a string or an array', $e->getMessage());
        }
        try {
            $sut->addHydrationSource('first', $callable);
            $this->fail('Expected exception');
        } catch (\BadMethodCallException $e) {
            $this->assertEquals('Duplicated source name first', $e->getMessage());
        }

        $this->assertEquals(2, $sut->unsetHydrationSource(['first', 'second']));
        $sources = $this->getValueFromProtected($sut, $reflected, 'sources');
        $this->assertInternalType('array', $sources);
        $this->assertCount(1, $sources);
        $this->assertArrayHasKey('third', $sources);
        $this->assertSame($callable2, $sources['third']);
        $this->assertEquals(1, $sut->unsetHydrationSource('third'));
        $sources = $this->getValueFromProtected($sut, $reflected, 'sources');
        $this->assertInternalType('array', $sources);
        $this->assertCount(0, $sources);
        $this->assertEquals(0, $sut->unsetHydrationSource('third'));
        $this->assertSame($sut, $sut->addHydrationSource('first', $callable), 'Should be okay');
        $this->assertSame($sut, $sut->addHydrationSource('second', $callable2), 'Should be okay');
        $sources = $this->getValueFromProtected($sut, $reflected, 'sources');
        $this->assertInternalType('array', $sources);
        $this->assertCount(2, $sources);
        try {
            $sut->unsetHydrationSource(123);
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('SourceName must be a string or an array', $e->getMessage());
        }
        $this->assertEquals(2, $sut->unsetAllHydrationSources());
        $sources = $this->getValueFromProtected($sut, $reflected, 'sources');
        $this->assertInternalType('array', $sources);
        $this->assertCount(0, $sources);
    }

    /**
     * Test conditionals.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @covers \Bairwell\Hydrator::addConditional
     * @covers \Bairwell\Hydrator::unsetConditional
     * @covers \Bairwell\Hydrator::unsetAllConditionals
     */
    public function testConditionals()
    {
        $sut       = new Hydrator();
        $reflected = new \ReflectionClass($sut);
        $this->assertInternalType('array', $this->getValueFromProtected($sut, $reflected, 'conditionals'));
        $this->assertEmpty($this->getValueFromProtected($sut, $reflected, 'conditionals'));
        $callable  = function () {
        };
        $callable2 = function () {
        };
        $this->assertSame($sut, $sut->addConditional('first', $callable), 'Should be okay');
        $sources = $this->getValueFromProtected($sut, $reflected, 'conditionals');
        $this->assertInternalType('array', $sources);
        $this->assertCount(1, $sources);
        $this->assertArrayHasKey('first', $sources);
        $this->assertSame($callable, $sources['first']);
        $this->assertSame($sut, $sut->addConditional(['second', 'third'], $callable2), 'Should be okay');
        $sources = $this->getValueFromProtected($sut, $reflected, 'conditionals');
        $this->assertInternalType('array', $sources);
        $this->assertCount(3, $sources);
        $this->assertArrayHasKey('first', $sources);
        $this->assertSame($callable, $sources['first']);
        $this->assertArrayHasKey('second', $sources);
        $this->assertSame($callable2, $sources['second']);
        $this->assertArrayHasKey('third', $sources);
        $this->assertSame($callable2, $sources['third']);
        try {
            $sut->addConditional(123, $callable);
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('Name must be a string or an array', $e->getMessage());
        }
        try {
            $sut->addConditional('first', $callable);
            $this->fail('Expected exception');
        } catch (\BadMethodCallException $e) {
            $this->assertEquals('Duplicated conditional name first', $e->getMessage());
        }
        $this->assertEquals(2, $sut->unsetConditional(['first', 'second']));
        $sources = $this->getValueFromProtected($sut, $reflected, 'conditionals');
        $this->assertInternalType('array', $sources);
        $this->assertCount(1, $sources);
        $this->assertArrayHasKey('third', $sources);
        $this->assertSame($callable2, $sources['third']);
        $this->assertEquals(1, $sut->unsetConditional('third'));
        $sources = $this->getValueFromProtected($sut, $reflected, 'conditionals');
        $this->assertInternalType('array', $sources);
        $this->assertCount(0, $sources);
        $this->assertEquals(0, $sut->unsetConditional('third'));
        $this->assertSame($sut, $sut->addConditional('first', $callable), 'Should be okay');
        $this->assertSame($sut, $sut->addConditional('second', $callable2), 'Should be okay');
        $sources = $this->getValueFromProtected($sut, $reflected, 'conditionals');
        $this->assertInternalType('array', $sources);
        $this->assertCount(2, $sources);
        try {
            $sut->unsetConditional(123);
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('Name must be a string or an array', $e->getMessage());
        }
        $this->assertEquals(2, $sut->unsetAllConditionals());
        $sources = $this->getValueFromProtected($sut, $reflected, 'conditionals');
        $this->assertInternalType('array', $sources);
        $this->assertCount(0, $sources);
    }

    /**
     * Test standardise string..
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @covers \Bairwell\Hydrator::standardiseString
     */
    public function testStandardiseString()
    {
        $sut       = new Hydrator();
        $reflected = new \ReflectionClass($sut);
        $method    = $reflected->getMethod('standardiseString');
        $method->setAccessible(true);
        $this->assertEquals('abc123', $method->invoke($sut, 'ABC 123'));
        $this->assertEquals('testingSystem', $method->invoke($sut, 'tEsTing sYsteM'));
        $this->assertEquals('testingSystem', $method->invoke($sut, 'tEsTing_sYsteM'));
        $this->assertEquals('testingSystem', $method->invoke($sut, 'tEsTing-sYsteM'));
        $this->assertEquals('xyzAbcDef', $method->invoke($sut, 'XyZ_abc-def'));
        try {
            $method->invoke($sut, '!');
        } catch (\Exception $e) {
            $this->assertEquals('Unable to standardise string - ended up too short', $e->getMessage());
        }
    }
    /**
     * Test getting the annotation reader.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @covers \Bairwell\Hydrator::getAnnotationReader
     */
    public function testMissingAnnotationReader() {
        $sut       = new Hydrator();
        $reflected = new \ReflectionClass($sut);
        $method    = $reflected->getMethod('getAnnotationReader');
        $method->setAccessible(true);
        try {
            $GLOBALS['BairwellClassExistsOverride']=function (string $className) {
                    return false;
            };
            $method->invoke($sut);
            $this->fail('Expected exception');
        } catch (\RuntimeException $e) {
            $this->assertEquals('Bairwell\Hydrator requires the packages doctrine/annotations and doctrine/cache to be installed.',$e->getMessage());
        }
        unset($GLOBALS['Bairwell_classExistsOverride']);
    }
    /**
     * Test getting the annotation reader.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @covers \Bairwell\Hydrator::getAnnotationReader
     */
    public function testGetAnnotationReader()
    {
        $sut       = new Hydrator();
        $reflected = new \ReflectionClass($sut);
        $method    = $reflected->getMethod('getAnnotationReader');
        $method->setAccessible(true);
        $this->assertNull($this->getValueFromProtected($sut, $reflected, 'annotationReader'));
        $reader = $method->invoke($sut);
        $this->assertInstanceOf('\Doctrine\Common\Annotations\Reader', $reader);
        $this->assertInstanceOf(
            '\Doctrine\Common\Annotations\Reader',
            $this->getValueFromProtected($sut, $reflected, 'annotationReader')
        );
        $this->assertSame($reader, $this->getValueFromProtected($sut, $reflected, 'annotationReader'));
        $dummy = $this->getMockForAbstractClass('\Doctrine\Common\Annotations\Reader');
        $sut   = new Hydrator(null, $dummy);
        $this->assertInstanceOf(
            '\Doctrine\Common\Annotations\Reader',
            $this->getValueFromProtected($sut, $reflected, 'annotationReader')
        );
        $this->assertSame($dummy, $this->getValueFromProtected($sut, $reflected, 'annotationReader'));
        $reader = $method->invoke($sut);
        $this->assertInstanceOf('\Doctrine\Common\Annotations\Reader', $reader);
        $this->assertSame($dummy, $reader);
        $this->assertInstanceOf(
            '\Doctrine\Common\Annotations\Reader',
            $this->getValueFromProtected($sut, $reflected, 'annotationReader')
        );
        $this->assertSame($dummy, $this->getValueFromProtected($sut, $reflected, 'annotationReader'));
    }

    /**
     * Test validation of conditions.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @covers \Bairwell\Hydrator::validateConditions
     */
    public function testValidateConditions()
    {
        $sut       = new Hydrator();
        $reflected = new \ReflectionClass($sut);
        $method    = $reflected->getMethod('validateConditions');
        $method->setAccessible(true);
        //
        $results = $method->invoke($sut, [], 'abc');
        $this->assertInternalType('array', $results);
        $this->assertEmpty($results);
        // invalid string check
        try {
            $method->invoke($sut, [123], 'jeff');
        } catch (AnnotationException $e) {
            $this->assertEquals(
                'Conditions must be an array of strings for jeff: encountered integer',
                $e->getMessage()
            );
        }
        // missing conditions
        try {
            $method->invoke($sut, ['aBc de!fG 123'], 'jeff');
        } catch (AnnotationException $e) {
            $this->assertEquals('Missing/unrecognised conditional "abcDefg123" in jeff', $e->getMessage());
        }
        $knownConditions = $reflected->getProperty('conditionals');
        $knownConditions->setAccessible(true);
        $knownConditions->setValue($sut, ['abc' => 'a', 'testerMcTest' => 'b', 'unUsed' => 'c']);
        $results = $method->invoke($sut, ['AbC', 'teStEr mc Test'], 'thing');
        $this->assertInternalType('array', $results);
        $this->assertEquals(['abc', 'testerMcTest'], $results);
    }

    /**
     * Test validation of sources.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @covers \Bairwell\Hydrator::validateSources
     */
    public function testValidateSources()
    {
        $sut       = new Hydrator();
        $reflected = new \ReflectionClass($sut);
        $method    = $reflected->getMethod('validateSources');
        $method->setAccessible(true);
        // empty
        try {
            $method->invoke($sut, [], 'abc');
        } catch (AnnotationException $e) {
            $this->assertEquals('No source specified in annotation for abc', $e->getMessage());
        }
        // invalid string check
        try {
            $method->invoke($sut, [123], 'jeff');
        } catch (AnnotationException $e) {
            $this->assertEquals('Sources must be an array of strings for jeff: encountered integer', $e->getMessage());
        }
        // missing conditions
        try {
            $method->invoke($sut, ['aBc de!fG 123'], 'jeff');
        } catch (AnnotationException $e) {
            $this->assertEquals('Missing/unrecognised source "abcDefg123" in jeff', $e->getMessage());
        }
        $knownSources = $reflected->getProperty('sources');
        $knownSources->setAccessible(true);
        $knownSources->setValue($sut, ['abc' => 'a', 'testerMcTest' => 'b', 'unUsed' => 'c']);
        $results = $method->invoke($sut, ['AbC', 'teStEr mc Test'], 'thing');
        $this->assertInternalType('array', $results);
        $this->assertEquals(['abc', 'testerMcTest'], $results);
    }

    /**
     * Test cache.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @uses   \Bairwell\Hydrator\CachedClass
     * @covers \Bairwell\Hydrator::getFromCache
     */
    public function testGetFromCacheNoCache()
    {
        $sut       = new Hydrator();
        $reflected = new \ReflectionClass($sut);
        $method    = $reflected->getMethod('getFromCache');
        $method->setAccessible(true);
        $cachePool = $reflected->getProperty('cachePool');
        $cachePool->setAccessible(true);
        /* @var \Bairwell\Hydrator\CachedClass $result */
        $result = $method->invoke($sut, 'abc');
        $this->assertInstanceOf('\Bairwell\Hydrator\CachedClass', $result);
        $this->assertEquals('', $result->getName());
    }
    /**
     * Test cache.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @uses   \Bairwell\Hydrator\CachedClass
     * @covers \Bairwell\Hydrator::getFromCache
     */
    public function testGetFromCacheWithCacheItemInCacheIncorrectItemReturns()
    {
        $validReturnItem = new CachedClass('tester');
        $validCacheItem  = $this->getMockForAbstractClass('\Psr\Cache\CacheItemInterface');
        $validCacheItem->expects($this->once())
                       ->method('isHit')
                       ->willReturn(true);
        $validCacheItem->expects($this->once())
                       ->method('get')
                       ->willReturn($validReturnItem);
        $fakeCache = $this->getMockForAbstractClass('\Psr\Cache\CacheItemPoolInterface');
        $fakeCache->expects($this->once())
                  ->method('getItem')
                  ->with('myCachePrefixAbcDef')
                  ->willReturn($validCacheItem);

        $sut       = new Hydrator(null,null,$fakeCache,'myCachePrefix');


        /* @var \Bairwell\Hydrator\CachedClass $result */
        $result = $sut->getFromCache('aBc_dEF');
        $this->assertInstanceOf('\Bairwell\Hydrator\CachedClass', $result);
        $this->assertEquals('', $result->getName());
    }
    /**
     * Test cache.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @uses   \Bairwell\Hydrator\CachedClass
     * @covers \Bairwell\Hydrator::getFromCache
     */
    public function testGetFromCacheWithCacheItemInCache()
    {
        $validReturnItem = new CachedClass('aBc_dEF');
        $validCacheItem  = $this->getMockForAbstractClass('\Psr\Cache\CacheItemInterface');
        $validCacheItem->expects($this->once())
                       ->method('isHit')
                       ->willReturn(true);
        $validCacheItem->expects($this->once())
                       ->method('get')
                       ->willReturn($validReturnItem);
        $fakeCache = $this->getMockForAbstractClass('\Psr\Cache\CacheItemPoolInterface');
        $fakeCache->expects($this->once())
                  ->method('getItem')
                  ->with('myCachePrefixAbcDef')
                  ->willReturn($validCacheItem);

        $sut       = new Hydrator(null,null,$fakeCache,'myCachePrefix');


        /* @var \Bairwell\Hydrator\CachedClass $result */
        $result = $sut->getFromCache('aBc_dEF');
        $this->assertInstanceOf('\Bairwell\Hydrator\CachedClass', $result);
        $this->assertEquals('aBc_dEF', $result->getName());
        $this->assertSame($validReturnItem, $result);
    }

    /**
     * Test cache - when the item we are looking for is not in the cache.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @uses   \Bairwell\Hydrator\CachedClass
     * @covers \Bairwell\Hydrator::getFromCache
     */
    public function testGetFromCacheWithUncachedItemInCache()
    {
        $validCacheItem = $this->getMockForAbstractClass('\Psr\Cache\CacheItemInterface');
        $validCacheItem->expects($this->once())
                       ->method('isHit')
                       ->willReturn(false);
        $fakeCache = $this->getMockForAbstractClass('\Psr\Cache\CacheItemPoolInterface');
        $fakeCache->expects($this->once())
                  ->method('getItem')
                  ->with('myCachePrefixAbcDef')
                  ->willReturn($validCacheItem);
        $sut       = new Hydrator(null,null,$fakeCache,'myCachePrefix');

        /* @var \Bairwell\Hydrator\CachedClass $result */
        $result = $sut->getFromCache('aBc_dEF');
        $this->assertInstanceOf('\Bairwell\Hydrator\CachedClass', $result);
        $this->assertEquals('', $result->getName());
    }

    /**
     * Test cache.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @uses   \Bairwell\Hydrator\CachedClass
     * @covers \Bairwell\Hydrator::getFromCache
     */
    public function testGetFromCacheWithCacheInvalidItemInCache()
    {

        $validReturnItem = '123';
        $validCacheItem  = $this->getMockForAbstractClass('\Psr\Cache\CacheItemInterface');
        $validCacheItem->expects($this->once())
                       ->method('isHit')
                       ->willReturn(true);
        $validCacheItem->expects($this->once())
                       ->method('get')
                       ->willReturn($validReturnItem);
        $validCacheItem->expects($this->once())
                       ->method('isHit')
                       ->willReturn(true);
        $fakeCache = $this->getMockForAbstractClass('\Psr\Cache\CacheItemPoolInterface');
        $fakeCache->expects($this->once())
                  ->method('getItem')
                  ->with('cacheNameAbcDef')
                  ->willReturn($validCacheItem);
        $sut       = new Hydrator(null,null,$fakeCache,'cacheName');


        /* @var \Bairwell\Hydrator\CachedClass $result */
        $result = $sut->getFromCache('aBc_dEF');
        $this->assertInstanceOf('\Bairwell\Hydrator\CachedClass', $result);
        $this->assertSame('', $result->getName());
    }

    /**
     * Test cache.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @uses   \Bairwell\Hydrator\CachedClass
     * @covers \Bairwell\Hydrator::saveToCache
     */
    public function testSaveToCacheNoCache()
    {
        $sut       = new Hydrator();
        $reflected = new \ReflectionClass($sut);
        $method    = $reflected->getMethod('saveToCache');
        $method->setAccessible(true);
        $faked = new CachedClass('faked');
        $this->assertFalse($method->invoke($sut, 'abc', $faked));
    }

    /**
     * Test cache.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @uses   \Bairwell\Hydrator\CachedClass
     * @covers \Bairwell\Hydrator::saveToCache
     */
    public function testSaveToCacheWithCache()
    {
        $faked = new CachedClass('faked');
        $validCacheItem = $this->getMockForAbstractClass('\Psr\Cache\CacheItemInterface');
        $validCacheItem->expects($this->once())
                       ->method('set')
                       ->with($faked);
        $validCacheItem->expects($this->once())
                       ->method('expiresAfter')
                       ->with(3600);
        $fakeCache = $this->getMockForAbstractClass('\Psr\Cache\CacheItemPoolInterface');
        $fakeCache->expects($this->once())
                  ->method('getItem')
                  ->with('myCachePrefixAbcDef')
                  ->willReturn($validCacheItem);
        $fakeCache->expects($this->once())
                  ->method('save')
                  ->with($validCacheItem);
        $sut       = new Hydrator(null,null,$fakeCache,'myCachePrefix');
        $reflected = new \ReflectionClass($sut);
        $method    = $reflected->getMethod('saveToCache');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($sut, 'aBc-dEf', $faked));
    }

    /**
     * Test parse property.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @uses   \Bairwell\Hydrator::getAnnotationReader
     * @uses   \Bairwell\Hydrator::validateSources
     * @uses   \Bairwell\Hydrator::validateConditions
     * @uses   \Bairwell\Hydrator\CachedClass
     * @uses   \Bairwell\Hydrator\CachedProperty
     * @uses   \Bairwell\Hydrator\Annotations\TypeCast\AsString
     * @uses   \Bairwell\Hydrator\Annotations\HydrateFrom
     * @covers \Bairwell\Hydrator::parseProperty
     */
    public function testParseProperty()
    {
        // prepare everything
        $cachedClass = new CachedClass('fakedClassName');
        // setup the reflection property
        $reflectionProperty =
            $this->getMockBuilder('\ReflectionProperty')
                 ->disableOriginalConstructor()
                 ->getMock();
        $reflectionProperty->expects($this->once())
                           ->method('getName')
                           ->willReturn('myPropertyName');
        $reflectionClassForProperty=$this->getMockBuilder('\ReflectionClass')
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $reflectionClassForProperty->expects($this->once())
                                   ->method('getName')
                                   ->willReturn('fakedClassName');
        $reflectionProperty->expects($this->once())
                           ->method('getDeclaringClass')
                           ->willReturn($reflectionClassForProperty);
        // setup the annotation reader

        $cast                    = new AsString();
        $hydrateFrom             = new HydrateFrom();
        $hydrateFrom->sources    = ['header', 'body'];
        $hydrateFrom->conditions = ['isjuly', 'isnotseptember'];
        $hydrateFrom->field      = 'jeff';
        $annotations             = [$cast, $hydrateFrom];
        $annotationReader        = $this->getMockForAbstractClass('\Doctrine\Common\Annotations\Reader');
        $annotationReader->expects($this->once())
                         ->method('getPropertyAnnotations')
                         ->with($reflectionProperty)
                         ->willReturn($annotations);
        // setup hydrator
        $sut       = new Hydrator(null, $annotationReader);
        $reflected = new \ReflectionClass($sut);
        // setup sources
        $knownSources = $reflected->getProperty('sources');
        $knownSources->setAccessible(true);
        $knownSources->setValue($sut, ['header' => 'a', 'body' => 'b', 'unUsed' => 'c']);
        // setup conditions
        $knownConditions = $reflected->getProperty('conditionals');
        $knownConditions->setAccessible(true);
        $knownConditions->setValue($sut, ['isjuly' => 'a', 'isnotseptember' => 'b']);
        $method = $reflected->getMethod('parseProperty');
        $method->setAccessible(true);
        $result = $method->invoke($sut, $reflectionProperty, $cachedClass);
        $this->assertSame($cachedClass, $result);
        $this->assertArrayHasKey('myPropertyName', $cachedClass);
        $propertyArray = $cachedClass['myPropertyName'];
        $this->assertInternalType('array', $propertyArray);
        $this->assertCount(1, $propertyArray);
        /* @var \Bairwell\Hydrator\CachedProperty $returnedProperty */
        $returnedProperty = $propertyArray[0];
        $this->assertEquals('myPropertyName', $returnedProperty->getName());
        $this->assertSame($cast, $returnedProperty->getCastAs());
        $this->assertSame($hydrateFrom, $returnedProperty->getFrom());
    }

    /**
     * Test parse property.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @uses   \Bairwell\Hydrator::getAnnotationReader
     * @uses   \Bairwell\Hydrator::validateSources
     * @uses   \Bairwell\Hydrator::validateConditions
     * @uses   \Bairwell\Hydrator\CachedClass
     * @uses   \Bairwell\Hydrator\CachedProperty
     * @uses   \Bairwell\Hydrator\Annotations\TypeCast\AsString
     * @uses   \Bairwell\Hydrator\Annotations\HydrateFrom
     * @covers \Bairwell\Hydrator::parseProperty
     */
    public function testParsePropertyEmptyField()
    {
        // prepare everything
        $cachedClass = new CachedClass('fakedClassName');
        // setup the reflection property
        $reflectionProperty =
            $this->getMockBuilder('\ReflectionProperty')
                 ->disableOriginalConstructor()
                 ->getMock();
        $reflectionProperty->expects($this->once())
                           ->method('getName')
                           ->willReturn('myPropertyName');

        $reflectionClassForProperty=$this->getMockBuilder('\ReflectionClass')
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $reflectionClassForProperty->expects($this->once())
                                   ->method('getName')
                                   ->willReturn('fakedClassName');
        $reflectionProperty->expects($this->once())
                           ->method('getDeclaringClass')
                           ->willReturn($reflectionClassForProperty);
        // setup the annotation reader

        $cast                    = new AsString();
        $hydrateFrom             = new HydrateFrom();
        $hydrateFrom->sources    = ['header', 'body'];
        $hydrateFrom->conditions = ['isjuly', 'isnotseptember'];
        $hydrateFrom->field      = '';
        $annotations             = [$cast, $hydrateFrom];
        $annotationReader        = $this->getMockForAbstractClass('\Doctrine\Common\Annotations\Reader');
        $annotationReader->expects($this->once())
                         ->method('getPropertyAnnotations')
                         ->with($reflectionProperty)
                         ->willReturn($annotations);
        // setup hydrator
        $sut       = new Hydrator(null, $annotationReader);
        $reflected = new \ReflectionClass($sut);
        // setup sources
        $knownSources = $reflected->getProperty('sources');
        $knownSources->setAccessible(true);
        $knownSources->setValue($sut, ['header' => 'a', 'body' => 'b', 'unUsed' => 'c']);
        // setup conditions
        $knownConditions = $reflected->getProperty('conditionals');
        $knownConditions->setAccessible(true);
        $knownConditions->setValue($sut, ['isjuly' => 'a', 'isnotseptember' => 'b']);
        $method = $reflected->getMethod('parseProperty');
        $method->setAccessible(true);
        $result = $method->invoke($sut, $reflectionProperty, $cachedClass);
        $this->assertSame($cachedClass, $result);
        $this->assertArrayHasKey('myPropertyName', $cachedClass);
        $propertyArray = $cachedClass['myPropertyName'];
        $this->assertInternalType('array', $propertyArray);
        $this->assertCount(1, $propertyArray);
        /* @var \Bairwell\Hydrator\CachedProperty $returnedProperty */
        $returnedProperty = $propertyArray[0];
        $this->assertEquals('myPropertyName', $returnedProperty->getName());
        $this->assertSame($cast, $returnedProperty->getCastAs());
        $hydratedFrom = $returnedProperty->getFrom();
        $this->assertEquals(['header', 'body'], $hydratedFrom->sources);
        $this->assertEquals(['isjuly', 'isnotseptember'], $hydratedFrom->conditions);
        $this->assertEquals('myPropertyName', $hydratedFrom->field);
    }

    /**
     * Test parse property.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @uses   \Bairwell\Hydrator::getAnnotationReader
     * @uses   \Bairwell\Hydrator::validateSources
     * @uses   \Bairwell\Hydrator::validateConditions
     * @uses   \Bairwell\Hydrator\CachedClass
     * @uses   \Bairwell\Hydrator\CachedProperty
     * @uses   \Bairwell\Hydrator\Annotations\TypeCast\AsString
     * @uses   \Bairwell\Hydrator\Annotations\HydrateFrom
     * @covers \Bairwell\Hydrator::parseProperty
     */
    public function testParsePropertyMultipleCasts()
    {
        // prepare everything
        $cachedClass = new CachedClass('faked');
        // setup the reflection property
        $reflectionProperty =
            $this->getMockBuilder('\ReflectionProperty')
                 ->disableOriginalConstructor()
                 ->getMock();
        $reflectionProperty->expects($this->once())
                           ->method('getName')
                           ->willReturn('myPropertyName');
        $reflectionClassForProperty=$this->getMockBuilder('\ReflectionClass')
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $reflectionClassForProperty->expects($this->once())
                                   ->method('getName')
                                   ->willReturn('xyz');
        $reflectionProperty->expects($this->once())
                           ->method('getDeclaringClass')
                           ->willReturn($reflectionClassForProperty);
        // setup the annotation reader

        $cast             = new AsString();
        $annotations      = [$cast, $cast];
        $annotationReader = $this->getMockForAbstractClass('\Doctrine\Common\Annotations\Reader');
        $annotationReader->expects($this->once())
                         ->method('getPropertyAnnotations')
                         ->with($reflectionProperty)
                         ->willReturn($annotations);
        // setup hydrator
        $sut       = new Hydrator(null, $annotationReader);
        $reflected = new \ReflectionClass($sut);
        $method    = $reflected->getMethod('parseProperty');
        $method->setAccessible(true);
        try {
            $method->invoke($sut, $reflectionProperty, $cachedClass);
            $this->fail('Expected exception');
        } catch (AnnotationException $e) {
            $this->assertEquals(
                'A property can only have zero or one Cast options - xyz::$myPropertyName has multiple',
                $e->getMessage()
            );
        }
    }

    /**
     * Test parse property.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @uses   \Bairwell\Hydrator::getAnnotationReader
     * @uses   \Bairwell\Hydrator::validateSources
     * @uses   \Bairwell\Hydrator::validateConditions
     * @uses   \Bairwell\Hydrator\CachedClass
     * @uses   \Bairwell\Hydrator\CachedProperty
     * @uses   \Bairwell\Hydrator\Annotations\TypeCast\AsString
     * @uses   \Bairwell\Hydrator\Annotations\HydrateFrom
     * @covers \Bairwell\Hydrator::parseProperty
     */
    public function testParsePropertyBadSource()
    {
        // prepare everything
        $cachedClass = new CachedClass('faked');
        // setup the reflection property
        $reflectionProperty =
            $this->getMockBuilder('\ReflectionProperty')
                 ->disableOriginalConstructor()
                 ->getMock();
        $reflectionProperty->expects($this->once())
                           ->method('getName')
                           ->willReturn('myPropertyName');

        $reflectionClassForProperty=$this->getMockBuilder('\ReflectionClass')
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $reflectionClassForProperty->expects($this->once())
                                   ->method('getName')
                                   ->willReturn('xyz');
        $reflectionProperty->expects($this->once())
                           ->method('getDeclaringClass')
                           ->willReturn($reflectionClassForProperty);
        // setup the annotation reader

        $cast                    = new AsString();
        $hydrateFrom             = new HydrateFrom();
        $hydrateFrom->sources    = ['header', 'body'];
        $hydrateFrom->conditions = ['isjuly', 'isnotseptember'];
        $hydrateFrom->field      = 'jeff';
        $annotations             = [$cast, $hydrateFrom];
        $annotationReader        = $this->getMockForAbstractClass('\Doctrine\Common\Annotations\Reader');
        $annotationReader->expects($this->once())
                         ->method('getPropertyAnnotations')
                         ->with($reflectionProperty)
                         ->willReturn($annotations);
        // setup hydrator
        $sut       = new Hydrator(null, $annotationReader);
        $reflected = new \ReflectionClass($sut);
        // setup sources
        $knownSources = $reflected->getProperty('sources');
        $knownSources->setAccessible(true);
        $knownSources->setValue($sut, ['header' => 'a', 'unUsed' => 'c']);
        // setup conditions
        $knownConditions = $reflected->getProperty('conditionals');
        $knownConditions->setAccessible(true);
        $knownConditions->setValue($sut, ['isjuly' => 'a', 'isnotseptember' => 'b']);
        $method = $reflected->getMethod('parseProperty');
        $method->setAccessible(true);
        try {
            $method->invoke($sut, $reflectionProperty, $cachedClass);
            $this->fail('Expected exception');
        } catch (AnnotationException $e) {
            $this->assertEquals('Missing/unrecognised source "body" in xyz::$myPropertyName', $e->getMessage());
        }
    }

    /**
     * Test parse property.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @uses   \Bairwell\Hydrator::getAnnotationReader
     * @uses   \Bairwell\Hydrator::validateSources
     * @uses   \Bairwell\Hydrator::validateConditions
     * @uses   \Bairwell\Hydrator\CachedClass
     * @uses   \Bairwell\Hydrator\CachedProperty
     * @uses   \Bairwell\Hydrator\Annotations\TypeCast\AsString
     * @uses   \Bairwell\Hydrator\Annotations\HydrateFrom
     * @covers \Bairwell\Hydrator::parseProperty
     */
    public function testParsePropertyBadConditional()
    {
        // prepare everything
        $cachedClass = new CachedClass('faked');
        // setup the reflection property
        $reflectionProperty =
            $this->getMockBuilder('\ReflectionProperty')
                 ->disableOriginalConstructor()
                 ->getMock();
        $reflectionProperty->expects($this->once())
                           ->method('getName')
                           ->willReturn('myPropertyName');

        $reflectionClassForProperty=$this->getMockBuilder('\ReflectionClass')
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $reflectionClassForProperty->expects($this->once())
                                   ->method('getName')
                                   ->willReturn('xyz');
        $reflectionProperty->expects($this->once())
                           ->method('getDeclaringClass')
                           ->willReturn($reflectionClassForProperty);
        // setup the annotation reader

        $cast                    = new AsString();
        $hydrateFrom             = new HydrateFrom();
        $hydrateFrom->sources    = ['header', 'body'];
        $hydrateFrom->conditions = ['isjuly', 'isnotseptember'];
        $hydrateFrom->field      = 'jeff';
        $annotations             = [$cast, $hydrateFrom];
        $annotationReader        = $this->getMockForAbstractClass('\Doctrine\Common\Annotations\Reader');
        $annotationReader->expects($this->once())
                         ->method('getPropertyAnnotations')
                         ->with($reflectionProperty)
                         ->willReturn($annotations);
        // setup hydrator
        $sut       = new Hydrator(null, $annotationReader);
        $reflected = new \ReflectionClass($sut);
        // setup sources
        $knownSources = $reflected->getProperty('sources');
        $knownSources->setAccessible(true);
        $knownSources->setValue($sut, ['header' => 'a', 'body' => 'b', 'unUsed' => 'c']);
        // setup conditions
        $knownConditions = $reflected->getProperty('conditionals');
        $knownConditions->setAccessible(true);
        $knownConditions->setValue($sut, ['isjuly' => 'a']);
        $method = $reflected->getMethod('parseProperty');
        $method->setAccessible(true);
        try {
            $result = $method->invoke($sut, $reflectionProperty, $cachedClass);
            $this->fail('Expected exception');
        } catch (AnnotationException $e) {
            $this->assertEquals(
                'Missing/unrecognised conditional "isnotseptember" in xyz::$myPropertyName',
                $e->getMessage()
            );
        }
    }
    /**
     * Test parse property.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @uses   \Bairwell\Hydrator::getAnnotationReader
     * @uses   \Bairwell\Hydrator::validateSources
     * @uses   \Bairwell\Hydrator::validateConditions
     * @uses   \Bairwell\Hydrator\CachedClass
     * @uses   \Bairwell\Hydrator\CachedProperty
     * @uses   \Bairwell\Hydrator\Annotations\TypeCast\AsString
     * @uses   \Bairwell\Hydrator\Annotations\HydrateFrom
     * @covers \Bairwell\Hydrator::parseProperty
     */
    public function testParsePropertyNoFromAnnotation()
    {
        // prepare everything
        $cachedClass = new CachedClass('faked');
        // setup the reflection property
        $reflectionProperty =
            $this->getMockBuilder('\ReflectionProperty')
                 ->disableOriginalConstructor()
                 ->getMock();
        $reflectionProperty->expects($this->once())
                           ->method('getName')
                           ->willReturn('myPropertyName');

        $reflectionClassForProperty=$this->getMockBuilder('\ReflectionClass')
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $reflectionClassForProperty->expects($this->once())
                                   ->method('getName')
                                   ->willReturn('xyz');
        $reflectionProperty->expects($this->once())
                           ->method('getDeclaringClass')
                           ->willReturn($reflectionClassForProperty);
        // setup the annotation reader

        $cast                    = new AsString();

        $annotations             = [$cast];
        $annotationReader        = $this->getMockForAbstractClass('\Doctrine\Common\Annotations\Reader');
        $annotationReader->expects($this->once())
                         ->method('getPropertyAnnotations')
                         ->with($reflectionProperty)
                         ->willReturn($annotations);
        // setup hydrator
        $sut       = new Hydrator(null, $annotationReader);
        $reflected = new \ReflectionClass($sut);
        // setup conditions
        $knownConditions = $reflected->getProperty('conditionals');
        $knownConditions->setAccessible(true);
        $knownConditions->setValue($sut, ['isjuly' => 'a', 'isnotseptember' => 'b']);
        $method = $reflected->getMethod('parseProperty');
        $method->setAccessible(true);
        $result = $method->invoke($sut, $reflectionProperty, $cachedClass);
        $this->assertSame($cachedClass, $result);
        $this->assertArrayNotHasKey('myPropertyName', $cachedClass);
    }
    /**
     * Test hydrate property - non object sent
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator\CachedProperty
     * @uses   \Bairwell\Hydrator\FailureList
     * @uses   \Bairwell\Hydrator\Annotations\HydrateFrom
     * @covers \Bairwell\Hydrator::hydrateSingleProperty
     */
    public function testHydrateNotObject() {
        $from=new HydrateFrom();
        $cachedProperty=new Hydrator\CachedProperty('className','propertyName',$from);
        $failureList=new Hydrator\FailureList();
        $sut=new Hydrator();
        try {
            $sut->hydrateSingleProperty($cachedProperty, 'string',$failureList);
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('HydrateSingleProperty must be passed an object as $object: got string',$e->getMessage());
        }
    }
    /**
     * Test hydrate property - non callable conditional
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @uses   \Bairwell\Hydrator::validateSources
     * @uses   \Bairwell\Hydrator::validateConditions
     * @uses   \Bairwell\Hydrator\CachedProperty
     * @uses   \Bairwell\Hydrator\Annotations\TypeCast\AsString
     * @uses   \Bairwell\Hydrator\Annotations\HydrateFrom
     * @uses   \Bairwell\Hydrator\FailureList
     * @covers \Bairwell\Hydrator::hydrateSingleProperty
     */
    public function testHydrateSinglePropertyNoncallableConditional() {
        $from=new HydrateFrom();
        $from->sources=['jeff','banks'];
        $from->conditions=['shouldbegreen'];
        $castAs=new AsString();
        $property=new Hydrator\CachedProperty('testClassName','myPropertyName',$from,$castAs);
        // setup hydrator
        $sut       = new Hydrator();
        $reflected = new \ReflectionClass($sut);
        // setup sources
        $knownSources = $reflected->getProperty('sources');
        $knownSources->setAccessible(true);
        $knownSources->setValue($sut, ['jeff' => 'a', 'banks' => 'b', 'unUsed' => 'c']);
        // setup conditionals
        $conditionals = $reflected->getProperty('conditionals');
        $conditionals->setAccessible(true);
        $conditionals->setValue($sut, ['shouldbegreen' => 'a','unUsed' => 'b']);
        $failure=new Hydrator\FailureList();

        $object=new \stdClass();
        try {
            $sut->hydrateSingleProperty($property,$object,$failure);
            $this->fail('Expected exception');
        } catch (\Exception $e) {
            $this->assertEquals('Conditional "shouldbegreen" is not callable when checking testClassName::$myPropertyName',
                $e->getMessage());
        }
    }
    /**
     * Test hydrate single property - non callable source.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @uses   \Bairwell\Hydrator::validateSources
     * @uses   \Bairwell\Hydrator::validateConditions
     * @uses   \Bairwell\Hydrator\CachedProperty
     * @uses   \Bairwell\Hydrator\Annotations\TypeCast\AsString
     * @uses   \Bairwell\Hydrator\Annotations\HydrateFrom
     * @uses   \Bairwell\Hydrator\FailureList
     * @covers \Bairwell\Hydrator::hydrateSingleProperty
     */
    public function testHydrateSinglePropertyNoncallableSource() {
        $from=new HydrateFrom();
        $from->sources=['jeff','banks'];
        $from->conditions=[];
        $castAs=new AsString();
        $property=new Hydrator\CachedProperty('testClassName','myPropertyName',$from,$castAs);
        // setup hydrator
        $sut       = new Hydrator();
        $reflected = new \ReflectionClass($sut);
        // setup sources
        $knownSources = $reflected->getProperty('sources');
        $knownSources->setAccessible(true);
        $knownSources->setValue($sut, ['jeff' => 'a', 'banks' => 'b', 'unUsed' => 'c']);
        // setup conditionals
        $conditionals = $reflected->getProperty('conditionals');
        $conditionals->setAccessible(true);
        $conditionals->setValue($sut, []);
        $failure=new Hydrator\FailureList();
        $object=new \stdClass();
        try {
            $sut->hydrateSingleProperty($property,$object,$failure);
            $this->fail('Expected exception');
        } catch (\Exception $e) {
            $this->assertEquals('Source "jeff" is not callable when hydrating testClassName::$myPropertyName',
                                $e->getMessage());
        }
    }
    /**
     * Test hydrate single property with failed conditional blocking hydration.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @uses   \Bairwell\Hydrator::validateSources
     * @uses   \Bairwell\Hydrator::validateConditions
     * @uses   \Bairwell\Hydrator\CachedProperty
     * @uses   \Bairwell\Hydrator\Annotations\TypeCast\AsString
     * @uses   \Bairwell\Hydrator\Annotations\HydrateFrom
     * @uses \Bairwell\Hydrator\FailureList
     * @covers \Bairwell\Hydrator::hydrateSingleProperty
     */
    public function testHydrateSinglePropertyFailedConditional() {
        $from=new HydrateFrom();
        $from->sources=['jeff','banks'];
        $from->conditions=['shouldbegreen'];
        $castAs=new AsString();
        $property=new Hydrator\CachedProperty('className','myPropertyName',$from,$castAs);
        // setup hydrator
        $sut       = new Hydrator();
        $reflected = new \ReflectionClass($sut);
        // setup sources
        $fakedSource=function($name) { return 'shouldNotSet'; };
        $knownSources = $reflected->getProperty('sources');
        $knownSources->setAccessible(true);
        $knownSources->setValue($sut, ['jeff' => $fakedSource, 'banks' => $fakedSource, 'unUsed' => $fakedSource]);
        // setup conditionals
        $conditionals = $reflected->getProperty('conditionals');
        $conditionals->setAccessible(true);
        $conditionals->setValue($sut, ['shouldbegreen' => function () { return false; },'unUsed' => 'b']);
        $failure=new Hydrator\FailureList();

        $object=new \stdClass();
        $sut->hydrateSingleProperty($property,$object,$failure);
        $this->assertFalse(property_exists($object,'myPropertyName'),'Should not have been set');

        $this->assertEmpty($failure);
    }
    /**
     * Test hydrate single property with a property name and no cast.
     *
     * Note the later source (banks) overwrites the earlier source (jeff).
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @uses   \Bairwell\Hydrator::validateSources
     * @uses   \Bairwell\Hydrator::validateConditions
     * @uses   \Bairwell\Hydrator\CachedProperty
     * @uses   \Bairwell\Hydrator\Annotations\HydrateFrom
     * @uses \Bairwell\Hydrator\FailureList
     * @covers \Bairwell\Hydrator::hydrateSingleProperty
     */
    public function testHydrateSinglePropertyWithPropertyName() {
        $from=new HydrateFrom();
        $from->sources=['jeff','banks'];
        $from->conditions=['shouldbegreen'];
        $property=new Hydrator\CachedProperty('className','myPropertyName',$from);
        // setup hydrator
        $sut       = new Hydrator();
        $reflected = new \ReflectionClass($sut);
        // setup sources
        $fakedJeff=function($name) { return 'source called jeff with '.$name; };
        $fakedBanks=function($name) { return 'source called banks with '.$name; };
        $fakedUnused=function($name) { return 'source called unusued with '.$name; };
        $knownSources = $reflected->getProperty('sources');
        $knownSources->setAccessible(true);
        $knownSources->setValue($sut, ['jeff' => $fakedJeff, 'banks' => $fakedBanks, 'unUsed' => $fakedUnused]);
        // setup conditionals
        $conditionals = $reflected->getProperty('conditionals');
        $conditionals->setAccessible(true);
        $conditionals->setValue($sut, ['shouldbegreen' => function () { return true; },'unUsed' => 'b']);
        //
        $object=new \stdClass();

        $failure=new Hydrator\FailureList();
        $sut->hydrateSingleProperty($property,$object,$failure);
        $this->assertTrue(property_exists($object,'myPropertyName'));
        $this->assertInternalType('string',$object->myPropertyName);
        $this->assertEquals('source called banks with myPropertyName',$object->myPropertyName);
        $this->assertEmpty($failure);
    }
    /**
     * Test hydrate single property with a from name and no cast.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @uses   \Bairwell\Hydrator::validateSources
     * @uses   \Bairwell\Hydrator::validateConditions
     * @uses   \Bairwell\Hydrator\CachedProperty
     * @uses   \Bairwell\Hydrator\Annotations\HydrateFrom
     * @uses \Bairwell\Hydrator\FailureList
     * @covers \Bairwell\Hydrator::hydrateSingleProperty
     */
    public function testHydrateSinglePropertyWithFromName() {
        $from=new HydrateFrom();
        $from->sources=['jeff','banks'];
        $from->conditions=['shouldbegreen'];
        $from->field='thingy';
        $property=new Hydrator\CachedProperty('className','myPropertyName',$from);
        // setup hydrator
        $sut       = new Hydrator();
        $reflected = new \ReflectionClass($sut);
        // setup sources
        $fakedJeff=function($name) { return 'source called jeff with '.$name; };
        $fakedBanks=function($name) { return null; };
        $fakedUnused=function($name) { return 'source called unusued with '.$name; };
        $knownSources = $reflected->getProperty('sources');
        $knownSources->setAccessible(true);
        $knownSources->setValue($sut, ['jeff' => $fakedJeff, 'banks' => $fakedBanks, 'unUsed' => $fakedUnused]);
        // setup conditionals
        $conditionals = $reflected->getProperty('conditionals');
        $conditionals->setAccessible(true);
        $conditionals->setValue($sut, ['shouldbegreen' => function () { return true; },'unUsed' => 'b']);
        //
        $object=new \stdClass();

        $failure=new Hydrator\FailureList();
        $sut->hydrateSingleProperty($property,$object,$failure);
        $this->assertTrue(property_exists($object,'myPropertyName'));
        $this->assertInternalType('string',$object->myPropertyName);
        $this->assertEquals('source called jeff with thingy',$object->myPropertyName);
        $this->assertEmpty($failure);
    }
    /**
     * Test hydrate single property with a property name and cast, but only second source has data.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @uses   \Bairwell\Hydrator::validateSources
     * @uses   \Bairwell\Hydrator::validateConditions
     * @uses   \Bairwell\Hydrator\CachedProperty
     * @uses   \Bairwell\Hydrator\Annotations\TypeCast\AsDateTime
     * @uses   \Bairwell\Hydrator\Annotations\HydrateFrom
     * @uses \Bairwell\Hydrator\FailureList
     * @uses \Bairwell\Hydrator\Annotations\TypeCast\CastBase
     * @covers \Bairwell\Hydrator::hydrateSingleProperty
     */
    public function testHydrateSinglePropertyWithPropertyNameAndCast() {
        $from=new HydrateFrom();
        $from->sources=['jeff','banks'];
        $from->conditions=['shouldbegreen'];
        $cast=new Hydrator\Annotations\TypeCast\AsDateTime();
        $property=new Hydrator\CachedProperty('className','myPropertyName',$from,$cast);
        // setup hydrator
        $sut       = new Hydrator();
        $reflected = new \ReflectionClass($sut);
        // setup sources
        $fakedJeff=function($name) { return null; };
        $fakedBanks=function($name) { return 1420421760; };
        $fakedUnused=function($name) { return 'source called unusued with '.$name; };
        $knownSources = $reflected->getProperty('sources');
        $knownSources->setAccessible(true);
        $knownSources->setValue($sut, ['jeff' => $fakedJeff, 'banks' => $fakedBanks, 'unUsed' => $fakedUnused]);
        // setup conditionals
        $conditionals = $reflected->getProperty('conditionals');
        $conditionals->setAccessible(true);
        $conditionals->setValue($sut, ['shouldbegreen' => function () { return true; },'unUsed' => 'b']);
        //
        $object=new \stdClass();
        $failure=new Hydrator\FailureList();
        $sut->hydrateSingleProperty($property,$object,$failure);
        $this->assertTrue(property_exists($object,'myPropertyName'));
        $this->assertInstanceOf('\DateTime',$object->myPropertyName,gettype($object->myPropertyName));
        $this->assertEquals(1420421760,$object->myPropertyName->format('U'));
        $this->assertEmpty($failure);
    }

    /**
     * Test hydrate single property with a property name and bad cast.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses   \Bairwell\Hydrator::standardiseString
     * @uses   \Bairwell\Hydrator::validateSources
     * @uses   \Bairwell\Hydrator::validateConditions
     * @uses   \Bairwell\Hydrator\CachedProperty
     * @uses   \Bairwell\Hydrator\Annotations\TypeCast\AsDateTime
     * @uses   \Bairwell\Hydrator\Annotations\HydrateFrom
     * @uses \Bairwell\Hydrator\FailureList
     * @uses \Bairwell\Hydrator\Failure
     * @uses \Bairwell\Hydrator\Annotations\TypeCast\CastBase
     * @covers \Bairwell\Hydrator::hydrateSingleProperty
     */
    public function testHydrateSinglePropertyWithPropertyNameAndBadCast() {
        $from=new HydrateFrom();
        $from->sources=['jeff','banks'];
        $from->conditions=['shouldbegreen'];
        $cast=new Hydrator\Annotations\TypeCast\AsDateTime();
        $property=new Hydrator\CachedProperty('className','myPropertyName',$from,$cast);
        // setup hydrator
        $sut       = new Hydrator();
        $reflected = new \ReflectionClass($sut);
        // setup sources
        $fakedJeff=function($name) { return null; };
        $fakedBanks=function($name) { return '1420421x760'; };
        $fakedUnused=function($name) { return 'source called unusued with '.$name; };
        $knownSources = $reflected->getProperty('sources');
        $knownSources->setAccessible(true);
        $knownSources->setValue($sut, ['jeff' => $fakedJeff, 'banks' => $fakedBanks, 'unUsed' => $fakedUnused]);
        // setup conditionals
        $conditionals = $reflected->getProperty('conditionals');
        $conditionals->setAccessible(true);
        $conditionals->setValue($sut, ['shouldbegreen' => function () { return true; },'unUsed' => 'b']);
        //
        $failure=new Hydrator\FailureList();
        $object=new \stdClass();
        $object->myPropertyName='should be untouched';
        $sut->hydrateSingleProperty($property,$object,$failure);
        $this->assertTrue(property_exists($object,'myPropertyName'));
        $this->assertEquals('should be untouched',$object->myPropertyName);
        $this->assertCount(1,$failure);
        /* @var \Bairwell\Hydrator\Failure $current */
        $current=$failure->current();
        $this->assertEquals('myPropertyName',$current->getInputField());
        $this->assertEquals('1420421x760',$current->getInputValue());
        $this->assertEquals(Hydrator\Annotations\TypeCast\CastBase::DATETIME_MUST_BE_ACCEPTED_FORMAT,$current->getMessage());
        $this->assertInternalType('array',$current->getTokens());
        $this->assertEmpty($current->getTokens());
        $this->assertEquals('banks',$current->getSource());
    }
    /**
     * Test getCachedClassForObject's exception.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @covers \Bairwell\Hydrator::getCachedClassForObject
     */
    public function testGetCachedClassForObjectException() {
        $sut=new Hydrator();
        $reflected=new \ReflectionClass($sut);
        $method=$reflected->getMethod('getCachedClassForObject');
        $method->setAccessible(true);
        try {
            $method->invoke($sut,'123');
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('getCachedClassForObject can only be called with objects: got string',$e->getMessage());
        }
    }
    /**
     * Test getCachedClassForObject with a valid cached item.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses \Bairwell\Hydrator::getFromCache
     * @uses \Bairwell\Hydrator::saveToCache
     * @uses \Bairwell\Hydrator::standardiseString
     * @uses \Bairwell\Hydrator\CachedClass
     * @covers \Bairwell\Hydrator::getCachedClassForObject
     *
     */
    public function testGetCachedClassForObjectCached() {
        $cachedItemInvalidName=new CachedClass('test');
        $cachePoolItem=$this->getMockForAbstractClass('\Psr\Cache\CacheItemInterface');
        $cachePoolItem->expects($this->once())
            ->method('isHit')
            ->willReturn(true);
        $cachePoolItem->expects($this->once())
                      ->method('get')
                      ->willReturn($cachedItemInvalidName);
        $cachePool=$this->getMockForAbstractClass('\Psr\Cache\CacheItemPoolInterface');
        $cachePool->expects($this->exactly(2))
            ->method('getItem')
            ->with('testPrefixStdclass')
            ->willReturn($cachePoolItem);
        $sut=new Hydrator(null,null,$cachePool,'testPrefix');
        $reflected=new \ReflectionClass($sut);
        $method=$reflected->getMethod('getCachedClassForObject');
        $method->setAccessible(true);
        $obj=new \stdClass();
        /* @var CachedClass $return */
        $return=$method->invoke($sut,$obj);
        $this->assertInstanceOf('\Bairwell\Hydrator\CachedClass',$return);
        $this->assertEquals('stdClass',$return->getName());
        $this->assertCount(0,$return);
    }
    /**
     * Test getCachedClassForObject with a valid cached item.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses \Bairwell\Hydrator::standardiseString
     * @uses \Bairwell\Hydrator\CachedClass
     * @covers \Bairwell\Hydrator::getCachedClassForObject
     */
    public function testGetCachedClassForObjectAnonymousClass() {
        $cachePool=$this->getMockForAbstractClass('\Psr\Cache\CacheItemPoolInterface');
        $cachePool->expects($this->never())
                  ->method('getItem');
        $sut=new Hydrator(null,null,$cachePool,'testPrefix');
        $reflected=new \ReflectionClass($sut);
        $method=$reflected->getMethod('getCachedClassForObject');
        $method->setAccessible(true);
        $obj=new class() { };
        $anonClassName=get_class($obj);
        /* @var CachedClass $return */
        $return=$method->invoke($sut,$obj);
        $this->assertInstanceOf('\Bairwell\Hydrator\CachedClass',$return);
        $this->assertEquals($anonClassName,$return->getName());
        $this->assertCount(0,$return);
    }
    /**
     * Test getCachedClassForObject with a valid cached item.
     *
     * @test
     * @uses   \Bairwell\Hydrator::__construct
     * @uses \Bairwell\Hydrator::standardiseString
     * @uses \Bairwell\Hydrator\CachedClass
     * @uses \Bairwell\Hydrator::getFromCache
     * @uses \Bairwell\Hydrator::getAnnotationReader
     * @uses \Bairwell\Hydrator::parseProperty
     *  @uses \Bairwell\Hydrator::saveToCache
     * @uses \Bairwell\Hydrator::parseProperty
     * @uses \Bairwell\Hydrator::validateSources
     * @uses \Bairwell\Hydrator::validateConditions
     * @uses \Bairwell\Hydrator::addHydrationSource
     * @uses \Bairwell\Hydrator\CachedProperty
     * @covers \Bairwell\Hydrator::getCachedClassForObject
     */
    public function testGetCachedClassForObject() {
        $annotationReader = $this->getMockForAbstractClass('\Doctrine\Common\Annotations\Reader');
        $annotationReader->expects($this->exactly(4))
            ->method('getPropertyAnnotations')
            ->willReturnCallback(function (\ReflectionProperty $property) {
                $return = [];
                switch ($property->getName()) {
                    case 'testProperty':
                        break;
                    case 'testNoRecognisedAnnotations':
                        break;
                    case 'testWithAnnotations':
                        $obj      = new \stdClass();
                        $return[] = $obj;
                        break;
                    case 'testIntCast':
                        $obj       = new Hydrator\Annotations\TypeCast\AsInt();
                        $return[] = $obj;
                        $obj=new \Bairwell\Hydrator\Annotations\HydrateFrom();
                        $obj->sources=['dummySource'];
                        $return[]=$obj;
                        break;
                    case 'testStringCast':
                        $obj       = new Hydrator\Annotations\TypeCast\AsString();
                        $return[] = $obj;
                        $obj=new \Bairwell\Hydrator\Annotations\HydrateFrom();
                        $obj->sources=['dummySource'];
                        $return[]=$obj;
                        break;
                    case 'testOther':
                        $obj=new \Bairwell\Hydrator\Annotations\HydrateFrom();
                        $obj->sources=['dummySource'];
                        $return[]=$obj;
                        break;
                    default:
                        throw new \Exception('Unrecognised property:'.$property->getName());
                }

                return $return;
            });

        $sut=new Hydrator(null,$annotationReader);
        $sut->addHydrationSource('dummySource',function  ($a) { return 'thingy'; } );
        $mockedObject=new Hydrator\MockedObject();
        $reflected=new \ReflectionClass($sut);
        $method=$reflected->getMethod('getCachedClassForObject');
        $method->setAccessible(true);
        /* @var CachedClass $return */
        $return=$method->invoke($sut,$mockedObject);
        $this->assertInstanceOf('\Bairwell\Hydrator\CachedClass',$return);
        $this->assertEquals('Bairwell\Hydrator\MockedObject',$return->getName());
        $this->assertCount(3,$return);
    }
    /**
     * Test hydrateObject.
     *
     * @test
     * @uses   \Bairwell\Hydrator
     * @covers \Bairwell\Hydrator::hydrateObject
     */
    public function testHydrateObjectBadObject() {
        $sut=new Hydrator();
        try {
            $a = 'abc';
            $sut->hydrateObject($a);
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('Hydrate must be passed an object for hydration',$e->getMessage());
        }
    }
    /**
     * Test hydrateObject (with being passed failure list).
     *
     * @test
     * @uses   \Bairwell\Hydrator
     * @uses \Bairwell\Hydrator\CachedClass
     * @uses \Bairwell\Hydrator\CachedProperty
     * @uses \Bairwell\Hydrator\Failure
     * @uses \Bairwell\Hydrator\FailureList
     * @uses \Bairwell\Hydrator\Annotations\TypeCast\CastBase
     * @uses \Bairwell\Hydrator\Annotations\TypeCast\AsInt
     * @uses \Bairwell\Hydrator\Annotations\TypeCast\AsString
     * @uses \Bairwell\Hydrator\Annotations\HydrateFrom
     * @covers \Bairwell\Hydrator::hydrateObject
     */
    public function testHydrateObject() {
        $sut=new Hydrator();
        $sut->addHydrationSource('dummySource',function ($fieldName) {
            switch ($fieldName) {
                case 'numbered':
                    return "1245";
                case 'stringed':
                    return 45;
                default:
                    throw new \Exception('Unexpected source field - part 1 '.$fieldName);
            }
        }
        );
        $sut->addHydrationSource('other',function ($fieldName) {
           switch ($fieldName) {
               case 'testOther':
                   return 'correct';
               default:
                   throw new \Exception('Unexpected source field for other part 1: '.$fieldName);
           }
        });
        $sut->addConditional('sunrisen',function () { return true; } );
        $sut->addConditional('moonrisen',function () { return false; } );

        $mockedObject=new Hydrator\MockedObject();
        $failures=new Hydrator\FailureList();
        $dummyFailure=new Hydrator\Failure();
        $dummyFailure->setInputField('unittest');
        $dummyFailure->setMessage('unittest made message');
        $dummyFailure->setSource('jeff');
        $failures->add($dummyFailure);
        $failures=$sut->hydrateObject($mockedObject,$failures);
        // check the failures
        $this->assertInstanceOf('\Bairwell\Hydrator\FailureList',$failures);
        $this->assertCount(1,$failures);
        $this->assertSame($dummyFailure,$failures[0]);
        // now check the contents
        $this->assertInstanceOf('\Bairwell\Hydrator\MockedObject',$mockedObject);
        $this->assertInternalType('integer',$mockedObject->testIntCast);
        $this->assertEquals(1245,$mockedObject->testIntCast);
        $this->assertNull($mockedObject->testStringCast);
        $this->assertInternalType('string',$mockedObject->testOther);
        $this->assertEquals('correct',$mockedObject->testOther);

        // okay, now let's repeat that with the same object but different sources and conditions.
        $sut->unsetAllHydrationSources();
        $sut->addHydrationSource(['dummySource','other'],function ($fieldName) {
            switch ($fieldName) {
                case 'numbered':
                    return null;
                case 'testOther':
                    return null;
                case 'stringed':
                    return 45;
                default:
                    throw new \Exception('Unexpected source field for second part:'.$fieldName);
            }
        }
        );
        $sut->unsetConditional('moonrisen');
        $sut->addConditional('moonrisen',function () { return true; } );
        $failures=$sut->hydrateObject($mockedObject,$failures);
        // check the failures
        $this->assertInstanceOf('\Bairwell\Hydrator\FailureList',$failures);
        $this->assertCount(1,$failures);
        $this->assertSame($dummyFailure,$failures[0]);
        // now check the contents
        $this->assertInstanceOf('\Bairwell\Hydrator\MockedObject',$mockedObject);
        $this->assertInternalType('integer',$mockedObject->testIntCast);
        $this->assertEquals(1245,$mockedObject->testIntCast);
        $this->assertInternalType('string',$mockedObject->testStringCast);
        $this->assertEquals('45',$mockedObject->testStringCast);
        $this->assertInternalType('string',$mockedObject->testOther);
        $this->assertEquals('correct',$mockedObject->testOther);
    }
    /**
     * Test hydrateObject (with being passed failure list).
     *
     * @test
     * @uses   \Bairwell\Hydrator
     * @uses \Bairwell\Hydrator\CachedClass
     * @uses \Bairwell\Hydrator\CachedProperty
     * @uses \Bairwell\Hydrator\Failure
     * @uses \Bairwell\Hydrator\FailureList
     * @uses \Bairwell\Hydrator\Annotations\TypeCast\CastBase
     * @uses \Bairwell\Hydrator\Annotations\TypeCast\AsInt
     * @uses \Bairwell\Hydrator\Annotations\TypeCast\AsString
     * @uses \Bairwell\Hydrator\Annotations\HydrateFrom
     * @covers \Bairwell\Hydrator::hydrateObject
     */
    public function testHydrateObjectNotPassedFailureList()
    {
        $sut = new Hydrator();
        $sut->addHydrationSource(
            'dummySource',
            function ($fieldName) {
                switch ($fieldName) {
                    case 'numbered':
                        return "1245";
                    case 'stringed':
                        return 45;
                    default:
                        throw new \Exception('Unexpected source field - part 1 '.$fieldName);
                }
            }
        );
        $sut->addHydrationSource(
            'other',
            function ($fieldName) {
                switch ($fieldName) {
                    case 'testOther':
                        return 'correct';
                    default:
                        throw new \Exception('Unexpected source field for other part 1: '.$fieldName);
                }
            }
        );
        $sut->addConditional(
            'sunrisen',
            function () {
                return true;
            }
        );
        $sut->addConditional(
            'moonrisen',
            function () {
                return false;
            }
        );

        $mockedObject = new Hydrator\MockedObject();

        $failures = $sut->hydrateObject($mockedObject);
        // check the failures
        $this->assertInstanceOf('\Bairwell\Hydrator\FailureList', $failures);
        $this->assertCount(0, $failures);
        // now check the contents
        $this->assertInstanceOf('\Bairwell\Hydrator\MockedObject', $mockedObject);
        $this->assertInternalType('integer', $mockedObject->testIntCast);
        $this->assertEquals(1245, $mockedObject->testIntCast);
        $this->assertNull($mockedObject->testStringCast);
        $this->assertInternalType('string', $mockedObject->testOther);
        $this->assertEquals('correct', $mockedObject->testOther);
    }
    protected function getValueFromProtected($sut, \ReflectionClass $reflected, string $propertyName)
    {
        $property = $reflected->getProperty($propertyName);
        $property->setAccessible(true);
        $value = $property->getValue($sut);

        return $value;
    }
}
