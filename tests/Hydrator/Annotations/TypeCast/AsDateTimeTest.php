<?php
/**
 * Test
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator\Annotations\TypeCast;

/**
 * Class AsDateTimeTest.
 * @uses \Bairwell\Hydrator\Annotations\TypeCast\AsDateTime
 * @uses \Bairwell\Hydrator\Annotations\TypeCast\CastBase
 */
class AsDateTimeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Testing annotations.
     *
     * @test
     */
    public function testAnnotations() {
        $sut=new AsDateTime();
        $reflection=new \ReflectionClass($sut);
        $comments=$reflection->getDocComment();
        $expected=preg_quote('@Annotation');
        $results=preg_match_all('/(\n|\r)\s*\*\s+'.$expected.'\s*(\n|\r)/', $comments, $matches);;
        $this->assertEquals(1,$results);
        $expected=preg_quote('@Target({"PROPERTY"})');
        $this->assertEquals(1,preg_match_all('/(\n|\r)\s*\*\s+'.$expected.'\s*(\n|\r)/', $comments, $matches));
        $property=$reflection->getProperty('allowNull');
        $comments=$property->getDocComment();
        $expected=preg_quote('@var ').'(bool|boolean)';
        $this->assertEquals(1,preg_match_all('/(\n|\r)\s*\*\s+'.$expected.'\s*(\n|\r)/', $comments, $matches));
        //
        $property=$reflection->getProperty('min');
        $comments=$property->getDocComment();
        $expected=preg_quote('@var \DateTime');
        $this->assertEquals(1,preg_match_all('/(\n|\r)\s*\*\s+'.$expected.'\s*(\n|\r)/', $comments, $matches));
        $property=$reflection->getProperty('max');
        //
        $comments=$property->getDocComment();
        $expected=preg_quote('@var \DateTime');
        $this->assertEquals(1,preg_match_all('/(\n|\r)\s*\*\s+'.$expected.'\s*(\n|\r)/', $comments, $matches));
    }
    /**
     * Testing inheritance.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsDateTime::__construct
     */
    public function testConstructor() {
        $sut=new AsDateTime();
        $this->assertInstanceOf('\Bairwell\Hydrator\Annotations\TypeCast\AsDateTime',$sut);
        $this->assertInstanceOf('\Bairwell\Hydrator\Annotations\TypeCast\CastBase',$sut);
        $min=new \DateTime('1970-01-01 00:00:00');
        $max=new \DateTime('2999-12-31 23:59:59');
        $this->assertEquals($min,$sut->min);
        $this->assertEquals($max,$sut->max);
        $this->assertTrue($sut->allowNull);

    }
    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsDateTime::cast
     */
    public function testCastInvalidDefault() {
        $sut=new AsDateTime();
        try {
            $sut->cast('abx','abc');
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('DefaultValue must be a \DateTime for DateTime casts',$e->getMessage());
        }
    }
    /**
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsDateTime::doCast
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsDateTime::attemptCast
     */
    public function testCastInvalidMin() {
        $sut=new AsDateTime();
        $sut->min='jeff';
        try {
            $sut->cast('a');
            $this->fail('Expected exception');
        } catch (\RuntimeException $e) {
            $this->assertEquals('AsDateTime min is invalid',$e->getMessage());
        }
        $sut->min=[];
        try {
            $sut->cast('a');
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('AttemptCast must be a \DateTime, string or a numeric',$e->getMessage());
        }
    }
    /**
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsDateTime::doCast
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsDateTime::attemptCast
     */
    public function testCastInvalidMax() {
        $sut=new AsDateTime();
        $sut->max='jeff';
        try {
            $sut->cast('a');
            $this->fail('Expected exception');
        } catch (\RuntimeException $e) {
            $this->assertEquals('AsDateTime max is invalid',$e->getMessage());
        }
        $sut->max=[];
        try {
            $sut->cast('a');
            $this->fail('Expected exception');
        } catch (\TypeError $e) {
            $this->assertEquals('AttemptCast must be a \DateTime, string or a numeric',$e->getMessage());
        }
    }
    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsDateTime::doCast
     */
    public function testCastInvalidTypes() {
        $sut=new AsDateTime();
        $this->assertFalse($sut->hasErrored());
        $this->assertNull($sut->cast([]));
        $this->assertTrue($sut->hasErrored());
        $this->assertSame(CastBase::ONLY_STRINGS_NUMERICS,$sut->getErrorMessage());
        $this->assertEmpty($sut->getErrorTokens());
        // now try with a different default
        $default=new \DateTime();
        $this->assertSame($default,$sut->cast([],$default));
        $this->assertTrue($sut->hasErrored());
        $this->assertSame(CastBase::ONLY_STRINGS_NUMERICS,$sut->getErrorMessage());
        $this->assertEmpty($sut->getErrorTokens());
    }
    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsDateTime::doCast
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsDateTime::attemptCast
     */
    public function testCastNotAccepted() {
        $sut=new AsDateTime();
        $this->assertFalse($sut->hasErrored());
        $this->assertNull($sut->cast('hello'));
        $this->assertTrue($sut->hasErrored());
        $this->assertSame(CastBase::DATETIME_MUST_BE_ACCEPTED_FORMAT,$sut->getErrorMessage());
        $this->assertEmpty($sut->getErrorTokens());
        // now try with a different default
        $default=new \DateTime();
        $this->assertSame($default,$sut->cast('thingy',$default));
        $this->assertTrue($sut->hasErrored());
        $this->assertSame(CastBase::DATETIME_MUST_BE_ACCEPTED_FORMAT,$sut->getErrorMessage());
        $this->assertEmpty($sut->getErrorTokens());
    }
    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsDateTime::doCast
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsDateTime::attemptCast
     */
    public function testCastAtom() {
        $expected=new \DateTime('2015-06-15T11:47:32+00:00');
        $value='2015-06-15T11:47:32+00:00';
        $sut=new AsDateTime();
        $this->assertEquals($expected,$sut->cast($value));
        $this->assertFalse($sut->hasErrored());
        // overflow check
        $this->assertEquals($expected,$sut->cast('2015-06-32T11:47:32+00:00',$expected),'Overflow check for Atom format');
        $this->assertTrue($sut->hasErrored(),'Overflow check for Atom format');
        $this->assertSame(CastBase::DATETIME_MUST_BE_ACCEPTED_FORMAT,$sut->getErrorMessage(),'Overflow check for Atom format');
        $this->assertEmpty($sut->getErrorTokens(),'Overflow check for Atom format');
    }
    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsDateTime::doCast
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsDateTime::attemptCast
     */
    public function testCastRfc() {
        $expected=new \DateTime('2015-06-15T11:47:32+00:00');
        $value='Mon, 15 Jun 2015 11:47:32 +0000';
        $sut=new AsDateTime();
        $this->assertEquals($expected,$sut->cast($value));
        $this->assertFalse($sut->hasErrored());
        // overflow check
        $this->assertEquals($expected,$sut->cast('Wed, 31 Jun 2015 11:47:32 +0000',$expected),'Overflow check for Rfc format');
        $this->assertTrue($sut->hasErrored(),'Overflow check for Rfc format');
        $this->assertSame(CastBase::DATETIME_MUST_BE_ACCEPTED_FORMAT,$sut->getErrorMessage(),'Overflow check for Rfc format');
        $this->assertEmpty($sut->getErrorTokens(),'Overflow check for Rfc format');
    }
    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsDateTime::doCast
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsDateTime::attemptCast
     */
    public function testCastUnix() {
        $expected=new \DateTime('2015-06-15T11:47:32+00:00');
        $value='1434368852';
        $sut=new AsDateTime();
        $this->assertEquals($expected,$sut->cast($value));
        $this->assertFalse($sut->hasErrored());
    }
    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsDateTime::doCast
     */
    public function testCastTooEarly() {
        $sut=new AsDateTime();
        $ourMin=new \DateTime('2000-01-01 12:34:56+00:00');
        $sut->min=$ourMin;
        $value='2000-01-02T11:47:32+00:00';
        $expected=new \DateTime($value);
        /* @var \DateTime $casted */
        $casted=$sut->cast($value);
        $this->assertEquals($expected,$casted,'Should have cast as after min date');
        $this->assertFalse($sut->hasErrored());
        $this->assertEquals('2000-01-02T11:47:32+00:00',$casted->format('c'));
        // now to check for value
        $value='1999-01-02T11:47:32+00:00';
        $this->assertSame($ourMin,$sut->min);
        $this->assertNull($sut->cast($value));
        $this->assertTrue($sut->hasErrored());
        $this->assertSame(CastBase::DATETIME_OUTSIDE_ACCEPTABLE_RANGE,$sut->getErrorMessage());
        $expectedTokens=['%min%'=>$sut->min->format('Y-m-d H:i:s'),'%max%'=>$sut->max->format('Y-m-d H:i:s')];
        $this->assertEquals($expectedTokens,$sut->getErrorTokens());
        // exact check
        $toPass=$sut->min->format('U');
        /* @var \DateTime $casted */
        $casted=$sut->cast($toPass);
        $this->assertSame($ourMin,$sut->min);
        $this->assertEquals($sut->min,$casted,'Should have cast as exactly min date of '.$toPass);
        $this->assertFalse($sut->hasErrored());
    }

    /**
     * Testing cast.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsDateTime::doCast
     */
    public function testCastTooLate() {
        $sut=new AsDateTime();
        $ourMax=new \DateTime('2001-04-05');
        $sut->max=$ourMax;
        $value='2000-04-01T11:47:32+00:00';
        $expected=new \DateTime($value);
        /* @var \DateTime $casted */
        $casted=$sut->cast($value);
        $this->assertEquals($expected,$casted,'Should have cast as before max date');
        $this->assertFalse($sut->hasErrored());
        $this->assertEquals('2000-04-01T11:47:32+00:00',$casted->format('c'));
        // now to check for value
        $value='2004-04-01T11:47:32+00:00';
        $this->assertNull($sut->cast($value));
        $this->assertTrue($sut->hasErrored());
        $this->assertSame(CastBase::DATETIME_OUTSIDE_ACCEPTABLE_RANGE,$sut->getErrorMessage());
        $expectedTokens=['%min%'=>$sut->min->format('Y-m-d H:i:s'),'%max%'=>$sut->max->format('Y-m-d H:i:s')];
        $this->assertEquals($expectedTokens,$sut->getErrorTokens());
        // exact check
        $toPass=$sut->max->format('U');
        /* @var \DateTime $casted */
        $casted=$sut->cast($toPass);
        $this->assertSame($ourMax,$sut->max);
        $this->assertEquals($sut->max,$casted,'Should have cast as exactly max date of '.$toPass);
        $this->assertFalse($sut->hasErrored());
    }
    /**
     * Testing defaults are within range.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\TypeCast\AsDateTime::doCast
     */
    public function testDefaultWithinRange() {
        $sut=new AsDateTime();
        $sut->min=new \DateTime('2001-04-03T11:23:33+00:00');
        $sut->max=new \DateTime('2001-04-05T13:45:11+00:00');
        $testTime='2001-04-03T11:23:33+00:00';
        $default=new \DateTime($testTime);
        $this->assertInstanceOf('\DateTime',$sut->cast($testTime,$default),'should be fine');
        try {
            $default=new \DateTime('2001-04-03T11:23:32+00:00');
            $sut->cast($testTime,$default);
        } catch (\RuntimeException $e) {
            $this->assertEquals('DefaultValue fails to reach min requirements for DateTime which is '.$sut->min->format('Y-m-d H:i:s').' was passed: '.$default->format('Y-m-d H:i:s'),$e->getMessage());
        }
        try {
            $default=new \DateTime('2001-04-05T13:45:11+00:01');
            $sut->cast($testTime,$default);
        } catch (\RuntimeException $e) {
            $this->assertEquals('DefaultValue exceed max requirements for DateTime which is '.$sut->max->format('Y-m-d H:i:s').' was passed: '.$default->format('Y-m-d H:i:s'),$e->getMessage());
        }
    }
}
