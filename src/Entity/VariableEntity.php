<?php declare(strict_types=1);

namespace AdamMarton\Stub\Entity;

use AdamMarton\Stub\Entity;
use AdamMarton\Stub\EntityInterface;
use AdamMarton\Stub\Storage;
use AdamMarton\Stub\Tokenizer;

final class VariableEntity extends Entity implements EntityInterface
{
    /**
     * @var string
     */
    protected $type = Storage::S_VARIABLE;

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
            $tokenizer->advanceTo([Tokenizer::SEMICOLON])
        );
    }

    private function format()
    {
        $signature = $this->data;
        $hasValue  = array_search('=', $signature);

        if ($hasValue) {
            array_walk(
                $signature,
                function (&$value, $key) {
                    if ($value === '=>') {
                        $value = ' => ';
                    }
                    if ($value === '=') {
                        $value = ' = ';
                    }
                    if ($value === '//') {
                        $value = '';
                    }
                }
            );

            return $this->pad() . implode(
                ' ',
                array_slice($signature, 0, $hasValue)
            ) . implode(
                '',
                array_slice($signature, $hasValue)
            ) . Tokenizer::SEMICOLON . Tokenizer::LINE_BREAK;
        }

        return $this->pad() . implode(' ', $signature) . Tokenizer::SEMICOLON . Tokenizer::LINE_BREAK;
    }
}
