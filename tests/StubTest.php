<?php

namespace Stub\Tests;

use PHPUnit\Framework\TestCase;
use Stub\Stub;

class StubTest extends TestCase
{
    public function testConstructorWithoutDirectory()
    {
        $this->expectException(\ArgumentCountError::class);
        new Stub();
    }

    public function testGenerateWithoutGenerator()
    {
        $this->expectException(\ArgumentCountError::class);
        $stub = new Stub('MockCode');
        $stub->generate('');
    }

    public function testGenerateWithDirectory()
    {
        $stubgen   = new Stub('tests\MockCode\empty');
        $generator = $stubgen->generate('stubs\mock', new \Stub\Generator);

        foreach ($generator->format() as $stub) {
            $this->assertEquals("<?php\n", $stub);
        }
    }

    public function testGenerateFunction()
    {
        $stubgen   = new Stub('tests\MockCode\function');
        $generator = $stubgen->generate('stubs\mock', new \Stub\Generator);

        foreach ($generator->format() as $stub) {
            $this->assertEquals("<?php\n\nfunction simpleFunction(\$arg = [])\n{\n}\n", $stub);
        }
    }

    public function testGenerateStringDefine()
    {
        $stubgen   = new Stub('tests\MockCode\string\define');
        $generator = $stubgen->generate('stubs\mock', new \Stub\Generator);

        foreach ($generator->format() as $stub) {
            $this->assertEquals("<?php\n\ndefine('STUBGEN', 'FTW');\n", $stub);
        }
    }

    public function testGenerateFinalClassObject()
    {
        $stubgen   = new Stub('tests\MockCode\objects\class-final');
        $generator = $stubgen->generate('stubs\mock', new \Stub\Generator);

        foreach ($generator->format() as $stub) {
            $this->assertEquals(
"<?php

namespace Stub\Tests\Objects\ClassObject;

final class ClassObjectTest
{
    /**
     * @var string
     */
    protected \$mockStringProperty;
}
",
                $stub
            );
        }
    }

    public function testGenerateAbstractClassObject()
    {
        $stubgen   = new Stub('tests\MockCode\objects\class-abstract');
        $generator = $stubgen->generate('stubs\mock', new \Stub\Generator);

        foreach ($generator->format() as $stub) {
            $this->assertEquals(
"<?php

namespace Stub\Tests\Objects\ClassObject;

abstract class ClassObjectTest
{
    /**
     * @var array
     */
    protected \$mockStringProperty = [
        1 => 'one',
        2 => 'two',
        3 => 'three',
        4 => 'four',
        5 => 'five',
        6 => 'six',
        7 => 'seven',
        8 => []
    ];
}
",
                $stub
            );
        }
    }

    public function testGenerateAbstractClassWithMethodsObject()
    {
        $stubgen   = new Stub('tests\MockCode\objects\class-methods');
        $generator = $stubgen->generate('stubs\mock', new \Stub\Generator);

        foreach ($generator->format() as $stub) {
            $this->assertEquals(
"<?php

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
    protected \$mockStringProperty = [];

    /**
     * Sample method to test method parsing features.
     */
    public function testingMethod(
        callable \$callable,
        callable \$otherCallable,
        callable \$justToForceLineBreaksAsWell,
        array \$andHereIsAnArray = []
    ) {
    }

    /**
     * An awesome abstract method you can implement!
     */
    abstract public function youCanDoIt(\$badWhitespace = array());
}
",
                $stub
            );
        }
    }
}
