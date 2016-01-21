<?php
/**
 * Test
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator\Annotations;

/**
 * Class HydrateFromTest.
 */
class HydrateFromTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Testing annotations.
     *
     * @test
     * @covers \Bairwell\Hydrator\Annotations\HydrateFrom
     */
    public function testAnnotations() {
        $sut=new HydrateFrom();
        $reflection=new \ReflectionClass($sut);
        $this->assertTrue($reflection->isFinal());
        $properties=$reflection->getDefaultProperties();
        $expectedProperties=['sources'=>[],'field'=>null,'conditions'=>[]];
        foreach ($expectedProperties as $k=>$v) {
            $this->assertArrayHasKey($k,$properties);
            $this->assertEquals($v,$properties[$k]);
        }
        $comments=$reflection->getDocComment();
        $expected=preg_quote('@Annotation');
        $results=preg_match_all('/(\n|\r)\s*\*\s+'.$expected.'\s*(\n|\r)/', $comments, $matches);;
        $this->assertEquals(1,$results);
        $expected=preg_quote('@Target({"PROPERTY"})');
        $this->assertEquals(1,preg_match_all('/(\n|\r)\s*\*\s+'.$expected.'\s*(\n|\r)/', $comments, $matches));
        //
        $property=$reflection->getProperty('sources');
        $comments=$property->getDocComment();
        $expected='@var\s+array';
        $this->assertEquals(1,preg_match_all('/(\n|\r)\s*\*\s+'.$expected.'\s*(\n|\r)/', $comments, $matches));
        $expected='@Required';
        $this->assertEquals(1,preg_match_all('/(\n|\r)\s*\*\s+'.$expected.'\s*(\n|\r)/', $comments, $matches));
        // field
        $property=$reflection->getProperty('field');
        $comments=$property->getDocComment();
        $expected='@var\s+string';
        $this->assertEquals(1,preg_match_all('/(\n|\r)\s*\*\s+'.$expected.'\s*(\n|\r)/', $comments, $matches));
        // conditions
        $property=$reflection->getProperty('conditions');
        $comments=$property->getDocComment();
        $expected='@var\s+array';
        $this->assertEquals(1,preg_match_all('/(\n|\r)\s*\*\s+'.$expected.'\s*(\n|\r)/', $comments, $matches));
    }
}
