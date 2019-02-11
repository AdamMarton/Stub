<?php declare(strict_types=1);

namespace Stub\Entity;

use Stub\Entity;
use Stub\EntityInterface;
use Stub\Storage;
use Stub\Tokenizer;
use Stub\Token\TokenIterator;
use Stub\Token\Traverse\Criteria;

final class NamespaceEntity extends Entity implements EntityInterface
{
    /**
     * @var string
     */
    protected $type = Storage::S_NAMESPACE;

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
        $this->data[] = $tokenIterator->seekUntil(new Criteria(Tokenizer::SEMICOLON));
    }

    /**
     * @return string
     */
    private function format() : string
    {
        return Tokenizer::LINE_BREAK . str_replace(
            [' \ ', ' '. Tokenizer::SEMICOLON],
            ['\\', Tokenizer::SEMICOLON],
            implode(' ', $this->current())
        ) . Tokenizer::LINE_BREAK;
    }
}
