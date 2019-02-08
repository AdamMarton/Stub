<?php declare(strict_types=1);

namespace AdamMarton\Stub\Entity;

use AdamMarton\Stub\Entity;
use AdamMarton\Stub\EntityInterface;
use AdamMarton\Stub\Storage;
use AdamMarton\Stub\Tokenizer;

final class UseEntity extends Entity implements EntityInterface
{
    /**
     * @var string
     */
    protected $type = Storage::S_USE;

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
        $seek = $tokenizer->seekTo(';');

        if ($seek[1] === Tokenizer::PARENTHESIS_OPEN) {
            $this->data = [];
            return;
        }

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
        if (!$this->data) {
            return '';
        }

        $signature = $this->data;
        $closing   = Tokenizer::SEMICOLON;

        if (in_array(Tokenizer::BRACKET_OPEN, $signature)) {
            $closing = Tokenizer::SEMICOLON . Tokenizer::LINE_BREAK . Tokenizer::BRACKET_CLOSE;
        }

        $use = str_replace(
            [' \ ', ' , '],
            ['\\', ', '],
            implode(' ', $signature)
        ) . $closing;

        return $this->indent === 0 ?  $use : (string) preg_replace('/^(.*)\{(.*)\}$/s', "    $1{\n       $2    }", $use);
    }
}
