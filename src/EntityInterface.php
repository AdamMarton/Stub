<?php declare(strict_types=1);

namespace AdamMarton\Stub;

interface EntityInterface
{
    public function __toString() : string;
    public function type() : string;

    /**
     * @param  Tokenizer $tokenizer
     * @return void
     */
    public function add(Tokenizer $tokenizer);

    public function setIndent(int $indent);
}
