<?php declare(strict_types=1);

namespace AdamMarton\Stub\Entity;

use AdamMarton\Stub\Entity;
use AdamMarton\Stub\EntityInterface;
use AdamMarton\Stub\Storage;
use AdamMarton\Stub\Tokenizer;
use AdamMarton\Stub\Token\TokenIterator;
use AdamMarton\Stub\Token\Traverse\Criteria;

final class NamespaceEntity extends Entity implements EntityInterface
{
    /**
     * @var string
     */
    protected $type = Storage::S_NAMESPACE;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->format();
    }

    /**
     * @param  TokenIterator $tokenIterator
     * @return void
     */
    public function add(TokenIterator $tokenIterator)
    {
        $this->data = $tokenIterator->seekUntil(new Criteria(Tokenizer::SEMICOLON));
    }

    /**
     * @return string
     */
    private function format() : string
    {
        return Tokenizer::LINE_BREAK . str_replace(
            [' \ ', ' '. Tokenizer::SEMICOLON],
            ['\\', Tokenizer::SEMICOLON],
            implode(' ', $this->data)
        ) . Tokenizer::LINE_BREAK;
    }
}
