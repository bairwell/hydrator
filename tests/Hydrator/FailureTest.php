<?php
/**
 * Test
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator;


/**
 * Class FailureTest.
 * @uses \Bairwell\Hydrator\Failure
 */
class FailureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @covers \Bairwell\Hydrator\Failure::__construct
     */
    public function testConstructor() {
        $sut=new Failure();
        $this->assertEquals('',$sut->getInputField());
        $this->assertEquals('',$sut->getMessage());
        $this->assertEquals([],$sut->getTokens());
        $this->assertEquals('',$sut->getSource());
        $this->assertEquals('',$sut->getInputValue());
    }
    /**
     * @test
     * @covers \Bairwell\Hydrator\Failure::getInputField
     * @covers \Bairwell\Hydrator\Failure::setInputField
     */
    public function testInputField() {
        $sut=new Failure();
        $this->assertEquals('',$sut->getInputField());
        $this->assertSame($sut,$sut->setInputField('tester'));
        $this->assertEquals('tester',$sut->getInputField());
    }
    /**
     * @test
     * @covers \Bairwell\Hydrator\Failure::getMessage
     * @covers \Bairwell\Hydrator\Failure::setMessage
     */
    public function testMessage() {
        $sut=new Failure();
        $this->assertEquals('',$sut->getMessage());
        $this->assertSame($sut,$sut->setMessage('tester'));
        $this->assertEquals('tester',$sut->getMessage());
    }
    /**
     * @test
     * @covers \Bairwell\Hydrator\Failure::getTokens
     * @covers \Bairwell\Hydrator\Failure::setTokens
     */
    public function testTokens() {
        $sut=new Failure();
        $this->assertEquals([],$sut->getTokens());
        $this->assertSame($sut,$sut->setTokens(['a','b']));
        $this->assertEquals(['a','b'],$sut->getTokens());
    }
    /**
     * @test
     * @covers \Bairwell\Hydrator\Failure::getSource
     * @covers \Bairwell\Hydrator\Failure::setSource
     */
    public function testSource() {
        $sut=new Failure();
        $this->assertEquals('',$sut->getSource());
        $this->assertSame($sut,$sut->setSource('tester'));
        $this->assertEquals('tester',$sut->getSource());
    }
    /**
     * @test
     * @covers \Bairwell\Hydrator\Failure::getInputValue
     * @covers \Bairwell\Hydrator\Failure::setInputValue
     */
    public function testInputValue() {
        $sut=new Failure();
        $this->assertEquals('',$sut->getInputValue());
        $this->assertSame($sut,$sut->setInputValue('tester'));
        $this->assertEquals('tester',$sut->getInputValue());
    }
}
