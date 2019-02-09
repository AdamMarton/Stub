<?php declare(strict_types=1);

namespace AdamMarton\Stub\Entity;

use AdamMarton\Stub\Entity;
use AdamMarton\Stub\EntityInterface;
use AdamMarton\Stub\Storage;
use AdamMarton\Stub\Tokenizer;
use AdamMarton\Stub\Token\TokenIterator;
use AdamMarton\Stub\Token\Traverse\Criteria;

final class UseEntity extends Entity implements EntityInterface
{
    /**
     * @var string
     */
    protected $type = Storage::S_USE;

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
        $seek = $tokenIterator->seekUntil(new Criteria(Tokenizer::SEMICOLON));

        if ($seek[1] === Tokenizer::PARENTHESIS_OPEN) {
            $this->data = [];
            return;
        }

        $this->data = $seek;
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
            [' \ ', ' , ', ' '. Tokenizer::SEMICOLON],
            ['\\', ', '. ''],
            implode(' ', $signature)
        ) . $closing;

        return $this->indent === 0 ?  $use : (string) preg_replace('/^(.*)\{(.*)\}$/s', "    $1{\n       $2    }", $use);
    }
}
