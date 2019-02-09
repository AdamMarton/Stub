<?php declare(strict_types=1);

namespace AdamMarton\Stub\Entity;

use AdamMarton\Stub\Entity;
use AdamMarton\Stub\EntityInterface;
use AdamMarton\Stub\Storage;
use AdamMarton\Stub\Tokenizer;
use AdamMarton\Stub\Token\TokenIterator;
use AdamMarton\Stub\Token\Traverse\Criteria;

final class VariableEntity extends Entity implements EntityInterface
{
    /**
     * @var string
     */
    protected $type = Storage::S_VARIABLE;

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
        $signature = $this->data;
        $hasValue  = array_search('=', $signature);

        if (is_int($hasValue) && $hasValue) {
            array_walk(
                $signature,
                function (string &$value, int $key) : void {
                    if ($value === '=>') {
                        $value = ' => ';
                    }
                    if ($value === '=') {
                        $value = ' = ';
                    }
                    if ($value === ',') {
                        $value = ', ';
                    }
                }
            );

            return $this->formatArray($this->pad() . implode(
                ' ',
                array_slice($signature, 0, $hasValue)
            ) . implode(
                '',
                array_slice($signature, $hasValue)
            ) . Tokenizer::LINE_BREAK);
        }

        return $this->pad() .
            str_replace(
                ' ' . Tokenizer::SEMICOLON,
                Tokenizer::SEMICOLON,
                implode(' ', $signature)
            ) . Tokenizer::LINE_BREAK;
    }

    /**
     * @param  string $variable
     * @return string $variable
     */
    private function formatArray(string $variable) : string
    {
        if (substr_count($variable, '[') && !substr_count($variable, ' = [];')) {
            $variable = (string) preg_replace(
                ['/\=\s\[/s', '/,\s/s', "/\n\s+\]\;/s"],
                [" = [\n" . $this->pad() . $this->pad(), ",\n" . $this->pad() . $this->pad(), "\n" . $this->pad() . '];'],
                $variable
            );
        }

        return $variable;
    }
}
