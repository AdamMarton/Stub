<?php

namespace Stub\Tests;

use PHPUnit\Framework\TestCase;
use Stub\Token\TokenIterator;
use Stub\Token\Traverse\Criteria;

class TokenIteratorTest extends TestCase
{
    public function testWithNoArgument()
    {
        $this->expectException(\ArgumentCountError::class);
        new TokenIterator();
    }

    public function testWithInvalidArgument()
    {
        $this->expectException(\TypeError::class);
        new TokenIterator('not-an-array');
    }

    public function testCurrent()
    {
        $iterator = new TokenIterator(
            [
                [
                    0,
                    1
                ]
            ]
        );
        $this->assertEquals('1', $iterator->current());
    }

    public function testKey()
    {
        $iterator = new TokenIterator(
            [
                [
                    0,
                    1
                ]
            ]
        );
        $this->assertEquals(0, $iterator->key());
    }

    public function testNext()
    {
        $iterator = new TokenIterator(
            [
                [
                    0,
                    1
                ],
                [
                    2,
                    3
                ]
            ]
        );
        $iterator->rewind();
        $iterator->next();
        $this->assertEquals('3', $iterator->current());
    }

    public function testReset()
    {
        $iterator = new TokenIterator(
            [
                [
                    0,
                    1
                ],
                [
                    2,
                    3
                ],
                [
                    4,
                    5
                ],
                [
                    6,
                    7
                ]
            ]
        );
        $iterator->reset(3);
        $this->assertEquals('7', $iterator->current());
    }

    public function testSeekUntil()
    {
        $iterator = new TokenIterator(
            [
                [
                    0,
                    1
                ],
                [
                    2,
                    3
                ],
                [
                    4,
                    5
                ],
                [
                    6,
                    7
                ]
            ]
        );
        $this->assertEquals([1, 3, 5, 7], $iterator->seekUntil(new Criteria([7])));
    }

    public function testType()
    {
        $iterator = new TokenIterator(
            [
                [
                    0,
                    1
                ]
            ]
        );
        $this->assertEquals(0, $iterator->type());
    }

    public function testValid()
    {
        $iterator = new TokenIterator(
            [
                [
                    0,
                    1
                ]
            ]
        );
        $this->assertEquals(true, $iterator->valid());
    }
}
