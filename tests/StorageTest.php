<?php

namespace Stub\Tests;

use PHPUnit\Framework\TestCase;
use Stub\Storage;
use Stub\Entity\StringEntity;
use Stub\Token\TokenIterator;

class StorageTest extends TestCase
{
    public function testAddNoArgument()
    {
        $this->expectException(\ArgumentCountError::class);
        $storage = new Storage();
        $storage->add();
    }

    public function testAddValidArgument()
    {
        $iterator  = new TokenIterator(
            [
                [0, 'define'],
                [0, '('],
                [0, 'MY_CONST'],
                [0, ','],
                [0, 'MY_CONST_VAR'],
                [0, ')'],
                [0, ';']
            ]
        );
        $entity = new StringEntity();
        $entity->add($iterator);
        $storage = new Storage();
        $storage->add($entity);
        $this->assertEquals(1, $storage->length());
    }
}
