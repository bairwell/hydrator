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
     * @covers \Bairwell\Hydrator\FailureList::offsetGet
     * @covers \Bairwell\Hydrator\FailureList::offsetSet
     * @uses \Bairwell\Hydrator\FailureList::count
     * @covers \Bairwell\Hydrator\FailureList::offsetUnset
     * @covers \Bairwell\Hydrator\FailureList::offsetExists
     * @uses \Bairwell\Hydrator\FailureList::add
     */
    public function testOffsetExceptions()
    {
        $sut=new FailureList();
        $this->assertEquals(0, $sut->count());
        $this->assertFalse($sut->offsetExists(3));
        try {
            $sut->offsetExists('abc');
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('Offset must be an integer', $e->getMessage());
        }
        $this->assertNull($sut->offsetGet(3));
        try {
            $sut->offsetGet('abc');
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('Offset must be an integer', $e->getMessage());
        }
        /* @var Failure $failure */
        $failure=$this->getMockBuilder('\Bairwell\Hydrator\Failure')->disableOriginalConstructor()->getMock();
        $sut->add($failure);
        try {
            $sut->offsetSet('abc', 123);
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('Offset must be an integer', $e->getMessage());
        }
        try {
            $sut->offsetSet(123, 123);
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('Value must be an instance of Failure: got integer', $e->getMessage());
        }
        try {
            $std = new \stdClass();
            $sut->offsetSet(123, $std);
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('Value must be an instance of Failure: got object stdClass', $e->getMessage());
        }
        try {
            $sut->offsetUnset('abc');
        } catch (\TypeError $e) {
            $this->assertEquals('Offset must be an integer',$e->getMessage());
        }
    }


    /**
     * Tests the offsets.
     *
     * @test
     * @uses \Bairwell\Hydrator\FailureList::__construct
     * @covers \Bairwell\Hydrator\FailureList::offsetGet
     * @covers \Bairwell\Hydrator\FailureList::offsetSet
     * @covers \Bairwell\Hydrator\FailureList::count
     * @covers \Bairwell\Hydrator\FailureList::offsetUnset
     * @covers \Bairwell\Hydrator\FailureList::offsetExists
     * @covers \Bairwell\Hydrator\FailureList::add
     */
    public function testOffset()
    {
        $sut = new FailureList();
        /* @var Failure $failure */
        $failure=$this->getMockBuilder('\Bairwell\Hydrator\Failure')->disableOriginalConstructor()->getMock();
        $sut->add($failure);
        $this->assertEquals(1,$sut->count());
        $this->assertTrue($sut->offsetExists(0));
        $this->assertFalse($sut->offsetExists(23));
        $this->assertSame($failure,$sut->offsetGet(0));
        // now add it in a set position
        /* @var Failure $failure */
        $failure2=$this->getMockBuilder('\Bairwell\Hydrator\Failure')->disableOriginalConstructor()->getMock();
        $sut->offsetSet(23,$failure2);
        $this->assertEquals(2,$sut->count());
        $this->assertTrue($sut->offsetExists(0));
        $this->assertTrue($sut->offsetExists(23));
        $this->assertSame($failure,$sut->offsetGet(0));
        $this->assertSame($failure2,$sut->offsetGet(23));

        // unset test
        $sut->offsetUnset(23);
        $this->assertEquals(1,$sut->count());
        $this->assertTrue($sut->offsetExists(0));
        $this->assertFalse($sut->offsetExists(23));
        $this->assertSame($failure,$sut->offsetGet(0));

    }

}
