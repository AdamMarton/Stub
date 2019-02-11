<?php

namespace Stub\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Stub\Entity\UseEntity;
use Stub\Token\TokenIterator;

class UseEntityTest extends TestCase
{
    /**
     * @covers Stub\Entity\UseEntity::__toString
     * @covers Stub\Entity\UseEntity::add
     * @covers Stub\Entity\UseEntity::format
     * @covers Stub\Entity\UseEntity::type
     */
    public function testAdd()
    {
        $iterator = new TokenIterator(
            [
                [
                    0,
                    'use'
                ],
                [
                    0,
                    'Namespace'
                ],
                [
                    0,
                    '\\'
                ],
                [
                    0,
                    'Class'
                ],
                [
                    0,
                    ';'
                ]
            ]
        );
        $use = new UseEntity();
        $use->add($iterator);
        $this->assertEquals('use Namespace\\Class;', (string) $use);
    }
}
