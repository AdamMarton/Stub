<?php declare(strict_types=1);

namespace Stub\Entity;

use Stub\Entity;
use Stub\EntityInterface;
use Stub\Storage;
use Stub\Tokenizer;
use Stub\Token\TokenIterator;
use Stub\Token\Traverse\Criteria;

final class FunctionEntity extends Entity implements EntityInterface
{
    /**
     * @var string
     */
    protected $type = Storage::S_FUNCTION;

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
        $this->data[] = $tokenIterator->seekUntil(new Criteria([Tokenizer::SEMICOLON, Tokenizer::BRACKET_OPEN]));
    }

    /**
     * @return string
     */
    private function format() : string
    {
        $signature  = $this->current();
        $returnType = '';
        $length     = -1;
        $name       = array_slice($signature, 0, (int) array_search(Tokenizer::PARENTHESIS_OPEN, $signature));
        $isAbstract = $signature[sizeof($signature)-1] === Tokenizer::SEMICOLON;

        if (in_array(':', $signature)) {
            $colonKey   = (int) array_search(':', $signature);
            $returnType = (string) $signature[$colonKey+1];
            $length     = -2;
        }

        $arguments  = array_slice($signature, (int) array_search(Tokenizer::PARENTHESIS_OPEN, $signature)+1, $length);
        $arguments  = $this->arguments($arguments);

        $signature  = $this->pad() .
            implode(' ', $name) .
            Tokenizer::PARENTHESIS_OPEN .
            implode(', ', $arguments) . $returnType;

        $signature .= !$isAbstract ?
            Tokenizer::LINE_BREAK . $this->pad() . Tokenizer::BRACKET_OPEN . Tokenizer::LINE_BREAK . $this->pad() . Tokenizer::BRACKET_CLOSE . Tokenizer::LINE_BREAK :
            Tokenizer::SEMICOLON . Tokenizer::LINE_BREAK;

        return $this->formatArguments($signature);
    }

    /**
     * @param  array $arguments
     * @return array
     */
    protected function arguments(array $arguments) : array
    {
        $arguments = explode(',', str_replace(['=', '):'], [' = ', ') : '], implode('', $arguments)));

        array_walk(
            $arguments,
            function (string &$value, int $key) : void {
                $value = preg_replace('/^(\w+)(\$\w+)/i', '$1 $2', $value);
            }
        );

        return $arguments;
    }

    /**
     * @param  string $signature
     * @return string $signature
     */
    private function formatArguments(string $signature) : string
    {
        if (strlen($signature) > 120) {
            $signature = (string) preg_replace(
                [
                    '/function\s(.*)\(/s',
                    '/,\s/s',
                    "/\)\n{$this->pad()}\{/s"
                ],
                [
                    "function $1(\n" . $this->pad() . $this->pad(),
                    ",\n" . $this->pad() . $this->pad(),
                    "\n{$this->pad()}) {"
                ],
                $signature
            );
        }

        return (string) preg_replace('/(\w)\$/s', "$1 $", $signature);
    }
}
