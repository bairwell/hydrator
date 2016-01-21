<?php
/**
 * Test
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator;

use \Bairwell\Hydrator\Annotations\HydrateFrom;
use \Bairwell\Hydrator\Annotations\TypeCast\CastBase;

/**
 * Class CachedPropertyTest.
 * @uses \Bairwell\Hydrator\CachedProperty
 * @uses \Bairwell\Hydrator\Annotations\HydrateFrom
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
        $hydrateFrom=new \Bairwell\Hydrator\Annotations\HydrateFrom();
        /* @var CastBase $castBase */
        $castBase=$this->getMockForAbstractClass('\Bairwell\Hydrator\Annotations\TypeCast\CastBase');
        $sut=new CachedProperty('testClass','testingName',$hydrateFrom);
        $this->assertEquals('testClass',$sut->getClassName());
        $this->assertEquals('testingName',$sut->getName());
        $this->assertSame($hydrateFrom,$sut->getFrom());
        $this->assertFalse($sut->hasCastAs());
        $sut=new CachedProperty('test2Class','testing2Name',$hydrateFrom,$castBase);
        $this->assertEquals('test2Class',$sut->getClassName());
        $this->assertEquals('testing2Name',$sut->getName());
        $this->assertSame($hydrateFrom,$sut->getFrom());
        $this->assertTrue($sut->hasCastAs());
        $this->assertSame($castBase,$sut->getCastAs());
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
        $hydrateFrom=new \Bairwell\Hydrator\Annotations\HydrateFrom();
        /* @var CastBase $castBase */
        $castBase = $this->getMockForAbstractClass('\Bairwell\Hydrator\Annotations\TypeCast\CastBase');
        $sut      = new CachedProperty('testClassName','testingName', $hydrateFrom);
        $this->assertFalse($sut->hasCastAs());
        $this->assertSame($sut,$sut->setCastAs($castBase));
        $this->assertTrue($sut->hasCastAs());
        $this->assertSame($castBase,$sut->getCastAs());
        $sut      = new CachedProperty('testClassName','testingName', $hydrateFrom,$castBase);
        $this->assertTrue($sut->hasCastAs());
        $this->assertSame($castBase,$sut->getCastAs());
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
        $hydrateFrom=new \Bairwell\Hydrator\Annotations\HydrateFrom();
        $hydrateFromTwo =new \Bairwell\Hydrator\Annotations\HydrateFrom();

        $sut      = new CachedProperty('testClassName','testingName', $hydrateFrom);
        $this->assertSame($hydrateFrom,$sut->getFrom());
        $this->assertSame($sut,$sut->setFrom($hydrateFromTwo));
        $this->assertSame($hydrateFromTwo,$sut->getFrom());
    }
}
