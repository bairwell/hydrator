<?php
/**
 * Test
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator;

use Bairwell\Hydrator\Failure;

/**
 * Class FailureListTest.
 * @uses \Bairwell\Hydrator\FailureList
 */
class FailureListTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @covers \Bairwell\Hydrator\FailureList::__construct
     */
    public function testConstructor() {
        $sut=new FailureList();
        $this->assertInstanceOf('\Iterator',$sut);
        $this->assertInstanceOf('\Countable',$sut);
    }
    /**
     * @test
     * @covers \Bairwell\Hydrator\FailureList::count
     * @covers \Bairwell\Hydrator\FailureList::add
     */
    public function testCountable() {
        $sut=new FailureList();
        $this->assertEquals(0,$sut->count());
        $this->assertEquals(0,count($sut));
        /* @var Failure $failure */
        $failure=$this->getMockBuilder('\Bairwell\Hydrator\Failure')->disableOriginalConstructor()->getMock();
        $sut->add($failure);
        $this->assertEquals(1,$sut->count());
        $this->assertEquals(1,count($sut));
        $sut->add($failure);
        $this->assertEquals(2,$sut->count());
        $this->assertEquals(2,count($sut));
    }
    /**
     * @test
     * @covers \Bairwell\Hydrator\FailureList::count
     * @covers \Bairwell\Hydrator\FailureList::add
     * @covers \Bairwell\Hydrator\FailureList::current
     * @covers \Bairwell\Hydrator\FailureList::next
     * @covers \Bairwell\Hydrator\FailureList::key
     * @covers \Bairwell\Hydrator\FailureList::valid
     * @covers \Bairwell\Hydrator\FailureList::rewind
     */
    public function testIterator() {
        $sut=new FailureList();
        $this->assertEquals(0,count($sut));
        /* @var Failure $first */
        $first=$this->getMockBuilder('\Bairwell\Hydrator\Failure')->disableOriginalConstructor()->getMock();
        /* @var Failure $second */
        $second=$this->getMockBuilder('\Bairwell\Hydrator\Failure')->disableOriginalConstructor()->getMock();
        /* @var Failure $third */
        $third=$this->getMockBuilder('\Bairwell\Hydrator\Failure')->disableOriginalConstructor()->getMock();
        /* @var Failure $fourth */
        $fourth=$this->getMockBuilder('\Bairwell\Hydrator\Failure')->disableOriginalConstructor()->getMock();
        $sut->add($first);
        $this->assertEquals(1,count($sut));
        $sut->add($second);
        $sut->add($third);
        $sut->add($fourth);
        $this->assertEquals(4,count($sut));
        $iterated=0;
        foreach ($sut as $k=>$v) {
            if (0===$k) {
                $this->assertSame($first,$v);
                $iterated++;
            } elseif (1===$k) {
                $this->assertSame($second,$v);
                $iterated++;
            } elseif (2===$k) {
                $this->assertSame($third,$v);
                $iterated++;
            } elseif (3===$k) {
                $this->assertSame($fourth,$v);
                $iterated++;
            } else {
                $this->fail('Unexpected key: '.$k);
            }
        }
        $this->assertEquals(4,$iterated);
    }

    /**
     * Tests the offsets.
     *
     * @test
     * @uses \Bairwell\Hydrator\FailureList::__construct
     * @uses \Bairwell\Hydrator\FailureList::getName
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
        $hydrateFrom    = new HydrateFrom();
        $storedProperty = new CachedProperty('test2', 'abc', $hydrateFrom);
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
}
