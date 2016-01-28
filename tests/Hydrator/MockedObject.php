<?php
/**
 * Test
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator;

use Bairwell\Hydrator\Annotations as Hydrate;

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
     * @Hydrate\AsInt
     * @Hydrate\From(sources="dummySource",field="numbered",conditions="sunrisen")
     */
    public $testIntCast;
    /**
     * Needs an annotation for casting.
     *
     * @Hydrate\AsString
     * @Hydrate\From(sources="dummySource",field="stringed",conditions="moonrisen")
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
     * @Hydrate\From(sources="other")
     */
    public $testOther;
}
