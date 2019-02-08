<?php declare(strict_types=1);

namespace AdamMarton\Stub\Entity;

use AdamMarton\Stub\Entity;
use AdamMarton\Stub\EntityInterface;
use AdamMarton\Stub\Storage;
use AdamMarton\Stub\Tokenizer;

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
     * @param  Tokenizer $tokenizer
     * @return void
     */
    public function add(Tokenizer $tokenizer)
    {
        $this->data = array_merge(
            [$tokenizer->getCurrentToken(1)],
            $tokenizer->advanceTo(Tokenizer::SEMICOLON)
        );
    }

    /**
     * @return string
     */
    private function format() : string
    {
        return
            Tokenizer::LINE_BREAK .
            str_replace(' \ ', '\\', implode(' ', $this->data)) .
            Tokenizer::SEMICOLON .
            Tokenizer::LINE_BREAK;
    }
}
