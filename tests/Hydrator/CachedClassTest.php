<?php
/**
 * Test
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator;

use Bairwell\Hydrator\CachedProperty;
use Bairwell\Hydrator\Annotations\From;

/**
 * Class CachedClassTest.
 * @uses \Bairwell\Hydrator\CachedProperty
 * @uses \Bairwell\Hydrator\Annotations\From
 */
class CachedClassTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @covers \Bairwell\Hydrator\CachedClass::__construct
     * @covers \Bairwell\Hydrator\CachedClass::getName
     */
    public function testInheritence() {
        $sut=new CachedClass('tester');
        $this->assertEquals('tester',$sut->getName());
        $this->assertInstanceOf('\Iterator',$sut);
        $this->assertInstanceOf('\ArrayAccess',$sut);
        $this->assertInstanceOf('\Countable',$sut);
    }
    /**
     * Tests the add.
     *
     * @test
     * @uses \Bairwell\Hydrator\CachedClass::__construct
     * @uses \Bairwell\Hydrator\CachedClass::getName
     * @covers \Bairwell\Hydrator\CachedClass::count
     * @covers \Bairwell\Hydrator\CachedClass::add
     * @covers \Bairwell\Hydrator\CachedClass::get
     * @covers \Bairwell\Hydrator\CachedClass::offsetGet
     * @covers \Bairwell\Hydrator\CachedClass::offsetExists
     **/
    public function testAdd() {
        $sut=new CachedClass('test2');
        $this->assertEquals('test2',$sut->getName());
        $this->assertEquals(0,$sut->count());
        $this->assertFalse($sut->offsetExists('abc'));
        $from=new From();
        $storedProperty=new CachedProperty('test2','abc',$from);
        $sut->add($storedProperty);
        $this->assertEquals(1,$sut->count());
        $this->assertTrue($sut->offsetExists('abc'));
        $this->assertInternalType('array',$sut->offsetGet('abc'));
        $this->assertCount(1,$sut->offsetGet('abc'));
        $this->assertEquals([$storedProperty],$sut->offsetGet('abc'));
        $storedProperty2=new CachedProperty('test2','abc',$from);
        $sut->add($storedProperty2);
        $this->assertEquals(1,$sut->count());
        $this->assertTrue($sut->offsetExists('abc'));
        $this->assertInternalType('array',$sut->offsetGet('abc'));
        $this->assertCount(2,$sut->offsetGet('abc'));
        $this->assertEquals([$storedProperty,$storedProperty2],$sut->offsetGet('abc'));
        $this->assertEquals([$storedProperty,$storedProperty2],$sut['abc']);
        $this->assertEquals([$storedProperty,$storedProperty2],$sut->get('abc'));
        $this->assertEquals([],$sut->get('stuff'));
        // error
        $storedProperty3=new CachedProperty('tedsdsdsst2','abc',$from);
        try {
            $sut->add($storedProperty3);
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('Cannot add property "abc" for class tedsdsdsst2 to class test2',$e->getMessage());
        }
    }
    /**
     * Tests the offsets.
     *
     * @test
     * @uses \Bairwell\Hydrator\CachedClass::__construct
     * @uses \Bairwell\Hydrator\CachedClass::getName
     * @covers \Bairwell\Hydrator\CachedClass::offsetGet
     * @covers \Bairwell\Hydrator\CachedClass::offsetSet
     * @uses \Bairwell\Hydrator\CachedClass::count
     * @covers \Bairwell\Hydrator\CachedClass::offsetUnset
     * @covers \Bairwell\Hydrator\CachedClass::offsetExists
     * @uses \Bairwell\Hydrator\CachedClass::add
     */
    public function testCachedClassOffsetExceptions()
    {
        $sut = new CachedClass('test2');
        $this->assertEquals('test2', $sut->getName());
        $this->assertEquals(0, $sut->count());
        $this->assertFalse($sut->offsetExists('abc'));
        try {
            $sut->offsetExists(123);
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('Offset must be a propertyName string', $e->getMessage());
        }
        $this->assertNull($sut->offsetGet('abc'));
        try {
            $sut->offsetGet(123);
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('Offset must be a propertyName string', $e->getMessage());
        }
        $from    = new From();
        $storedProperty = new CachedProperty('test2', 'abc', $from);
        try {
            $sut->offsetSet(123, 123);
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('Offset must be a propertyName string', $e->getMessage());
        }
        try {
            $sut->offsetSet('abc', 123);
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('Value must be a CachedProperty: got integer', $e->getMessage());
        }
        try {
            $std = new \stdClass();
            $sut->offsetSet('abc', $std);
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('Value must be a CachedProperty: got object stdClass', $e->getMessage());
        }
        try {
            $sut->offsetUnset(123);
        } catch (\TypeError $e) {
            $this->assertEquals('Offset must be a propertyName string',$e->getMessage());
        }
    }
    /**
     * Tests the offsets.
     *
     * @test
     * @uses \Bairwell\Hydrator\CachedClass::__construct
     * @uses \Bairwell\Hydrator\CachedClass::getName
     * @uses \Bairwell\Hydrator\CachedClass::offsetGet
     * @covers \Bairwell\Hydrator\CachedClass::offsetSet
     * @uses \Bairwell\Hydrator\CachedClass::count
     * @uses \Bairwell\Hydrator\CachedClass::offsetUnset
     * @uses \Bairwell\Hydrator\CachedClass::offsetExists
     * @uses \Bairwell\Hydrator\CachedClass::add
     */
    public function testCachedClassOffsetOverwrite()
    {
        $sut = new CachedClass('test2');
        $this->assertEquals('test2', $sut->getName());
        $this->assertEquals(0, $sut->count());
        $this->assertFalse($sut->offsetExists('abc'));
        $from    = new From();
        $storedProperty = new CachedProperty('test2', 'abc', $from);
        $sut->add($storedProperty);
        $this->assertEquals(1,$sut->count());
        $this->assertTrue($sut->offsetExists('abc'));
        $this->assertInternalType('array',$sut->offsetGet('abc'));
        $this->assertCount(1,$sut->offsetGet('abc'));
        $this->assertSame($storedProperty,$sut->offsetGet('abc')[0]);
        // try to overwrite - should fail
        try {
            $sut->offsetSet('abc',$storedProperty);
            $this->fail('Expected exception');
        } catch (\RuntimeException $e) {
            $this->assertEquals('Offset abc already exists',$e->getMessage());
        }
    }
    /**
     * Tests the offsets.
     *
     * @test
     * @uses \Bairwell\Hydrator\CachedClass::__construct
     * @uses \Bairwell\Hydrator\CachedClass::getName
     * @covers \Bairwell\Hydrator\CachedClass::offsetGet
     * @covers \Bairwell\Hydrator\CachedClass::offsetSet
     * @covers \Bairwell\Hydrator\CachedClass::count
     * @covers \Bairwell\Hydrator\CachedClass::offsetUnset
     * @covers \Bairwell\Hydrator\CachedClass::offsetExists
     * @covers \Bairwell\Hydrator\CachedClass::add
     */
    public function testCachedClassOffset()
    {
        $sut = new CachedClass('test2');
        $from    = new From();
        $storedProperty = new CachedProperty('test2', 'abc', $from);
        $sut->add($storedProperty);
        $this->assertEquals(1,$sut->count());
        $this->assertTrue($sut->offsetExists('abc'));
        $this->assertInternalType('array',$sut->offsetGet('abc'));
        $this->assertCount(1,$sut->offsetGet('abc'));
        $this->assertSame($storedProperty,$sut->offsetGet('abc')[0]);
        // add a second
        $second=new CachedProperty('test2','abc',$from);
        $sut->add($second);
        $this->assertEquals(1,$sut->count());
        $this->assertInternalType('array',$sut->offsetGet('abc'));
        $this->assertCount(2,$sut->offsetGet('abc'));
        $this->assertSame($storedProperty,$sut->offsetGet('abc')[0]);
        $this->assertSame($second,$sut->offsetGet('abc')[1]);
        // add a third with a different name
        $third=new CachedProperty('test2','test',$from);
        $sut->offsetSet('test',$third);
        $this->assertEquals(2,$sut->count());
        $this->assertInternalType('array',$sut->offsetGet('test'));
        $this->assertCount(1,$sut->offsetGet('test'));
        $this->assertSame($third,$sut->offsetGet('test')[0]);
        // unset test
        $sut->offsetUnset('abc');
        $this->assertEquals(1,$sut->count());
        $this->assertInternalType('array',$sut->offsetGet('test'));
        $this->assertCount(1,$sut->offsetGet('test'));
        $this->assertSame($third,$sut->offsetGet('test')[0]);
        $this->assertNull($sut->offsetGet('abc'));

    }

    /**
     * Tests the offsets.
     *
     * @test
     * @uses \Bairwell\Hydrator\CachedClass::__construct
     * @uses \Bairwell\Hydrator\CachedClass::getName
     * @uses \Bairwell\Hydrator\CachedClass::add
     * @covers \Bairwell\Hydrator\CachedClass::offsetSet
     * @uses \Bairwell\Hydrator\CachedClass::count
     * @covers \Bairwell\Hydrator\CachedClass::current
     * @covers \Bairwell\Hydrator\CachedClass::next
     * @covers \Bairwell\Hydrator\CachedClass::key
     * @covers \Bairwell\Hydrator\CachedClass::valid
     * @covers \Bairwell\Hydrator\CachedClass::rewind
     */
    public function testCachedClassIterator()
    {
        $sut = new CachedClass('test3');
        $this->assertEquals('test3', $sut->getName());
        $from=new From();
        $storedProperty=
        $first=new CachedProperty('test3','abc',$from);
        $second=new CachedProperty('test3','abc',$from);
        $third=new CachedProperty('test3','def',$from);
        $fourth=new CachedProperty('test3','def',$from);
        $sut->add($first);
        $sut->add($second);
        $sut->add($third);
        $sut->add($fourth);
        $this->assertCount(2,$sut);
        $iterations=0;
        foreach ($sut as $k=>$v) {
            if ('abc'===$k) {
                $this->assertEquals([$first,$second],$v);
                $iterations++;
            } elseif ('def'===$k) {
                $this->assertEquals([$third,$fourth],$v);
                $iterations++;
            } else {
                $this->fail('Unexpected key:'.$k);
            }
        }
        $this->assertEquals(2,$iterations);

    }
}
