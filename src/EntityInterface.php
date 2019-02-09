<?php declare(strict_types=1);

namespace AdamMarton\Stub;

use AdamMarton\Stub\Token\TokenIterator;

interface EntityInterface extends \Iterator
{
    /**
     * @return string
     */
    public function __toString() : string;

    /**
     * @return string
     */
    public function type() : string;

    /**
     * @param  TokenIterator $tokenIterator
     * @return void
     */
    public function add(TokenIterator $tokenIterator);

    /**
     * @param  int  $indent
     * @return void
     */
    public function setIndent(int $indent);
}
