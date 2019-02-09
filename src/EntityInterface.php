<?php declare(strict_types=1);

namespace AdamMarton\Stub;

use AdamMarton\Stub\Token\TokenIterator;

interface EntityInterface extends \Iterator
{
    public function __toString() : string;
    public function type() : string;

    /**
     * @param  TokenIterator $tokenIterator
     * @return void
     */
    public function add(TokenIterator $tokenIterator);
    public function setIndent(int $indent);
}
