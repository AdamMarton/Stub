<?php declare(strict_types=1);

namespace AdamMarton\Stub\Entity;

use AdamMarton\Stub\Entity;
use AdamMarton\Stub\EntityInterface;
use AdamMarton\Stub\Storage;
use AdamMarton\Stub\Tokenizer;
use AdamMarton\Stub\Token\TokenIterator;

final class DocblockEntity extends Entity implements EntityInterface
{
    /**
     * @var string
     */
    protected $type = Storage::S_DOCBLOCK;

    /**
     * @var string
     */
    protected $data = '';

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
        $this->data = $tokenIterator->current();
    }

    /**
     * @return string
     */
    private function format() : string
    {
        return $this->pad() . $this->indent(str_replace('	', '    ', $this->data));
    }
}
