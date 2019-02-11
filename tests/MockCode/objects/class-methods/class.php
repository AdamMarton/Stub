<?php

namespace Stub\Tests\Objects\ClassObject;

use DummyClass;
use DummyTrait;

abstract class ClassObjectTest
{
    use Helper, Unhelper {
        stillHelper as CoolHelper;
    }

    /**
     * @var array
     */
    protected $mockStringProperty = [];

    /**
     * Sample method to test method parsing features.
     */
    public function testingMethod(callable $callable, callable $otherCallable, callable $justToForceLineBreaksAsWell, array $andHereIsAnArray = [ ]) {
        //
        /* Some random comment before testing lamda functions */
        $lambda = function () {
            return new class {
                public $excitingProperty = true;
            };
        };
    }

    /**
     * An awesome abstract method you can implement!
     */
    abstract public function youCanDoIt( $badWhitespace = array());
}
