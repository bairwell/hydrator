<?php
/**
 * Test
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator;

use \Bairwell\Hydrator\Annotations\From;
use \Bairwell\Hydrator\Annotations\AsBase;

/**
 * Class CachedPropertyTest.
 * @uses \Bairwell\Hydrator\CachedProperty
 * @uses \Bairwell\Hydrator\Annotations\From
 */
class CachedPropertyTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test constructor.
     *
     * @test
     * @covers \Bairwell\Hydrator\CachedProperty::__construct
     * @covers \Bairwell\Hydrator\CachedProperty::getName
     * @covers \Bairwell\Hydrator\CachedProperty::setName
     * @covers \Bairwell\Hydrator\CachedProperty::getClassName
     * @covers \Bairwell\Hydrator\CachedProperty::setClassName
     */
    public function testConstructor() {
        $from=new \Bairwell\Hydrator\Annotations\From();
        /* @var AsBase $AsBase */
        $AsBase=$this->getMockForAbstractClass('\Bairwell\Hydrator\Annotations\AsBase');
        $sut=new CachedProperty('testClass','testingName',$from);
        $this->assertEquals('testClass',$sut->getClassName());
        $this->assertEquals('testingName',$sut->getName());
        $this->assertSame($from,$sut->getFrom());
        $this->assertFalse($sut->hasCastAs());
        $sut=new CachedProperty('test2Class','testing2Name',$from,\ReflectionProperty::IS_PUBLIC,$AsBase);
        $this->assertEquals('test2Class',$sut->getClassName());
        $this->assertEquals('testing2Name',$sut->getName());
        $this->assertSame($from,$sut->getFrom());
        $this->assertTrue($sut->hasCastAs());
        $this->assertSame($AsBase,$sut->getCastAs());
        $this->assertSame($sut,$sut->setName('jeff'));
        $this->assertEquals('jeff',$sut->getName());
        $this->assertSame($sut,$sut->setClassName('thingy'));
        $this->assertEquals('thingy',$sut->getClassName());
    }


    /**
     * Test castAs..
     *
     * @test
     * @covers \Bairwell\Hydrator\CachedProperty::setCastAs
     * @covers \Bairwell\Hydrator\CachedProperty::hasCastAs
     * @covers \Bairwell\Hydrator\CachedProperty::getCastAs
     */
    public function testCastAs()
    {
        $from=new \Bairwell\Hydrator\Annotations\From();
        /* @var AsBase $AsBase */
        $AsBase = $this->getMockForAbstractClass('\Bairwell\Hydrator\Annotations\AsBase');
        $sut      = new CachedProperty('testClassName','testingName', $from);
        $this->assertFalse($sut->hasCastAs());
        $this->assertSame($sut,$sut->setCastAs($AsBase));
        $this->assertTrue($sut->hasCastAs());
        $this->assertSame($AsBase,$sut->getCastAs());
        $sut      = new CachedProperty('testClassName','testingName', $from,\ReflectionProperty::IS_PUBLIC,$AsBase);
        $this->assertTrue($sut->hasCastAs());
        $this->assertSame($AsBase,$sut->getCastAs());
    }

    /**
     * Test castAs..
     *
     * @test
     * @covers \Bairwell\Hydrator\CachedProperty::setFrom
     * @covers \Bairwell\Hydrator\CachedProperty::getFrom
     */
    public function testFrom()
    {
        $from=new \Bairwell\Hydrator\Annotations\From();
        $fromTwo =new \Bairwell\Hydrator\Annotations\From();

        $sut      = new CachedProperty('testClassName','testingName', $from);
        $this->assertSame($from,$sut->getFrom());
        $this->assertSame($sut,$sut->setFrom($fromTwo));
        $this->assertSame($fromTwo,$sut->getFrom());
    }
}
