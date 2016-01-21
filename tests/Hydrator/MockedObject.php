<?php
/**
 * Test
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator;

use Bairwell\Hydrator\Annotations\TypeCast;
use Bairwell\Hydrator\Annotations\HydrateFrom;

class MockedObject {

    public $testProperty;

    /**
     * Should have no recognised annotations.
     *
     * @var string Something.
     */
    public $testNoRecognisedAnnotations;

    /**
     * Needs an annotation for casting.
     *
     * @TypeCast\AsInt
     * @HydrateFrom(sources="dummySource",field="numbered",conditions="sunrisen")
     */
    public $testIntCast;
    /**
     * Needs an annotation for casting.
     *
     * @TypeCast\AsString
     * @HydrateFrom(sources="dummySource",field="stringed",conditions="moonrisen")
     */
    public $testStringCast;
    /**
     * Private: should not be returned.
     *
     * @var string
     */
    private $shouldNotBeReturned;


    /**
     * Needs an annotation for casting.
     *
     * @HydrateFrom(sources="other")
     */
    public $testOther;
}
