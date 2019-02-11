<?php

namespace Stub\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Stub\Entity\FunctionEntity;
use Stub\Token\TokenIterator;

class FunctionEntityTest extends TestCase
{
    public function testAdd()
    {
        $iterator = new TokenIterator(
            [
                [
                    0, 'public'
                ],
                [
                    0, 'function'
                ],
                [
                    0, 'testAbstract'
                ],
                [
                    0, '('
                ],
                [
                    0, ')'
                ],
                [
                    0, ':'
                ],
                [
                    0, 'bool'
                ],
                [
                    0, ';'
                ]
            ]
        );
        $use = new FunctionEntity();
        $use->add($iterator);
        $this->assertEquals("public function testAbstract() : bool;\n", (string) $use);
    }
}
