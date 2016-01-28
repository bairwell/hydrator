<?php
/**
 * Test
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator;

use Bairwell\Hydrator\Annotations as Hydrate;

class MockedArrayObject {

    /**
     * Simple array
     *
     * @Hydrate\AsArray
     * @Hydrate\From(sources="dummy",field="simple",arrayStyles="csv,pipes"))
     * @var array
     */
    public $simple;

}
