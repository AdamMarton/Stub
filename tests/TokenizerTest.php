<?php

namespace Stub\Tests;

use PHPUnit\Framework\TestCase;
use Stub\Tokenizer;

class TokenizerTest extends TestCase
{
    public function testArgumentCountError()
    {
        $this->expectException(\ArgumentCountError::class);
        new Tokenizer();
    }

    public function testTypeError()
    {
        $this->expectException(\TypeError::class);
        new Tokenizer([], []);
    }

    public function testString()
    {
        $tokenizer = new Tokenizer(
            "<?php\ndefine('STUB_IMPORTANT_VAR', 'hello world!');",
            call_user_func_array(
                [$this, 'logCallback'],
                [function ($arg) {
                    return $arg;
                }]
            )
        );
        $this->assertEquals(
"<?php

define('STUB_IMPORTANT_VAR', 'hello world!');
",
            $tokenizer->parse()
        );
    }

    public function testNamespace()
    {
        $tokenizer = new Tokenizer(
            "<?php\nnamespace My\Awesome\Library;",
            call_user_func_array(
                [$this, 'logCallback'],
                [function ($arg) {
                    return $arg;
                }]
            )
        );
        $this->assertEquals(
"<?php

namespace My\Awesome\Library;
",
            $tokenizer->parse()
        );
    }

    public function testUse()
    {
        $tokenizer = new Tokenizer(
            "<?php\nuse Important\Object\From\Elsewhere;",
            call_user_func_array(
                [$this, 'logCallback'],
                [function ($arg) {
                    return $arg;
                }]
            )
        );
        $this->assertEquals(
"<?php

use Important\Object\From\Elsewhere;
",
            $tokenizer->parse()
        );
    }

    public function testTrait()
    {
        $tokenizer = new Tokenizer(
            "<?php\ntrait MyTrait {}",
            call_user_func_array(
                [$this, 'logCallback'],
                [function ($arg) {
                    return $arg;
                }]
            )
        );
        $this->assertEquals(
"<?php

trait MyTrait
{
}
",
            $tokenizer->parse()
        );
    }

    public function testInterface()
    {
        $tokenizer = new Tokenizer(
            "<?php\ninterface MyInterface {}",
            call_user_func_array(
                [$this, 'logCallback'],
                [function ($arg) {
                    return $arg;
                }]
            )
        );
        print($tokenizer->parse());
        $this->assertEquals(
"<?php

interface MyInterface
{
}
",
            $tokenizer->parse()
        );
    }

    public function testDocComment()
    {
        $tokenizer = new Tokenizer(
"<?php
/**
 * DocComment
 */",
            call_user_func_array(
                [$this, 'logCallback'],
                [function ($arg) {
                    return $arg;
                }]
            )
        );
        $this->assertEquals(
"<?php

/**
 * DocComment
 */
",
            $tokenizer->parse()
        );
    }

    public function testAbstractFinal()
    {
        $tokenizer = new Tokenizer(
"<?php
abstract class MyAbstractClass {}",
            call_user_func_array(
                [$this, 'logCallback'],
                [function ($arg) {
                    return $arg;
                }]
            )
        );
        $this->assertEquals(
"<?php

abstract class MyAbstractClass
{
}
",
            $tokenizer->parse()
        );
    }

    public function testAbstractFinalMethod()
    {
        $tokenizer = new Tokenizer(
"<?php
abstract class MyAbstractClass {
    final function finalMethod(){
    }
}",
            call_user_func_array(
                [$this, 'logCallback'],
                [function ($arg) {
                    return $arg;
                }]
            )
        );
        $this->assertEquals(
"<?php

abstract class MyAbstractClass
{
    final function finalMethod()
    {
    }
}
",
            $tokenizer->parse()
        );
    }

    public function testVarConst()
    {
        $tokenizer = new Tokenizer(
"<?php
abstract class MyAbstractClass {
    var \$deprecated;
    const IMPORTANT = true;
}",
            call_user_func_array(
                [$this, 'logCallback'],
                [function ($arg) {
                    return $arg;
                }]
            )
        );
        $this->assertEquals(
"<?php

abstract class MyAbstractClass
{
    var \$deprecated;

    const IMPORTANT = true;
}
",
            $tokenizer->parse()
        );
    }

    public function testHandleMethodProperty()
    {
        $tokenizer = new Tokenizer(
"<?php
abstract class MyAbstractClass {
    var \$deprecated;
    const IMPORTANT = true;
    public \$public;
    protected \$protected;
    private \$private;
    public function publicMethod() {
    }
    protected function protectedMethod() {
        \$lambda = function () {
            return true;
        };
    }
    private function privateMethod() {
        // You cannot access me
    }
}",
            call_user_func_array(
                [$this, 'logCallback'],
                [function ($arg) {
                    return $arg;
                }]
            )
        );
        $this->assertEquals(
"<?php

abstract class MyAbstractClass
{
    var \$deprecated;

    const IMPORTANT = true;

    public \$public;

    protected \$protected;

    private \$private;

    public function publicMethod()
    {
    }

    protected function protectedMethod()
    {
    }

    private function privateMethod()
    {
    }
}
",
            $tokenizer->parse()
        );
    }

    private function logCallback($arg)
    {
        return $arg;
    }
}
