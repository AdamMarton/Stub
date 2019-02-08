<?php declare(strict_types=1);

namespace AdamMarton\Stub\Entity;

use AdamMarton\Stub\Entity;
use AdamMarton\Stub\EntityInterface;
use AdamMarton\Stub\Storage;
use AdamMarton\Stub\Tokenizer;

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
     * @param  Tokenizer $tokenizer
     * @return void
     */
    public function add(Tokenizer $tokenizer)
    {
        $this->data = $tokenizer->getCurrentToken(1);
    }

    private function format()
    {
        return $this->indent(str_replace('	', '    ', $this->data));
    }
}
