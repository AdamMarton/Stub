<?php declare(strict_types=1);

namespace Stub\Entity;

use Stub\Entity;
use Stub\EntityInterface;
use Stub\Storage;
use Stub\Tokenizer;
use Stub\Token\TokenIterator;

final class DocblockEntity extends Entity implements EntityInterface
{
    /**
     * @var string
     */
    protected $type = Storage::S_DOCBLOCK;

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
        $this->data[] = $tokenIterator->current();
    }

    /**
     * @return string
     */
    private function format() : string
    {
        return $this->pad() . (string) preg_replace(
            [
                '/     \*/',
                "/\/\*\*\s\*/s",
                "/(\w+)\s\*\//s"
            ],
            [
                "\n     *",
                "/**\n *",
                "$1\n */\n"
            ],
            $this->indent(str_replace('	', '    ', implode('', $this->current())))
        );
    }
}
