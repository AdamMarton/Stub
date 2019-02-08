<?php declare(strict_types=1);

namespace AdamMarton\Stub\Entity;

use AdamMarton\Stub\Entity;
use AdamMarton\Stub\EntityInterface;
use AdamMarton\Stub\Storage;
use AdamMarton\Stub\Tokenizer;

final class FunctionEntity extends Entity implements EntityInterface
{
    /**
     * @var string
     */
    protected $type = Storage::S_FUNCTION;

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
            $tokenizer->advanceTo([Tokenizer::SEMICOLON, Tokenizer::BRACKET_OPEN])
        );
    }

    /**
     * @return string
     */
    private function format() : string
    {
        $signature  = $this->data;
        $returnType = '';
        $ofset      = -1;
        $name       = array_slice($signature, 0, array_search(Tokenizer::PARENTHESIS_OPEN, $signature));
        $isAbstract = in_array('abstract', $name);

        if ($signature[sizeof($signature)-2] === ':') {
            $returnType = ' : ' . array_pop($signature);
            $ofset      = -2;
        }

        $arguments  = array_slice($signature, array_search(Tokenizer::PARENTHESIS_OPEN, $signature)+1, $ofset);
        $arguments  = $this->arguments($arguments);

        $signature  =
            $this->pad() .
            implode(' ', $name) .
            Tokenizer::PARENTHESIS_OPEN .
            implode(', ', $arguments) .
            Tokenizer::PARENTHESIS_CLOSE .
            $returnType;

        $signature .= !$isAbstract ?
            Tokenizer::LINE_BREAK . $this->pad() . Tokenizer::BRACKET_OPEN . Tokenizer::LINE_BREAK . $this->pad() . Tokenizer::BRACKET_CLOSE . Tokenizer::LINE_BREAK :
            Tokenizer::SEMICOLON . Tokenizer::LINE_BREAK;

        return $signature;
    }

    /**
     * @param  array $arguments
     * @return array
     */
    protected function arguments(array $arguments) : array
    {
        //$arguments = array_slice($arguments, array_search(Tokenizer::PARENTHESIS_OPEN, $arguments)+1, -1);
        $arguments = explode(',', str_replace(['='], [' = '], implode('', $arguments)));

        array_walk(
            $arguments,
            function (&$value, $key) {
                $value = preg_replace('/^(\w+)(\$\w+)/i', '$1 $2', $value);
            }
        );

        return $arguments;
    }
}
