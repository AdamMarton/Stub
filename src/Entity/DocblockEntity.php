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
     * @param  Tokenizer $tokenizer
     * @return void
     */
    public function add(Tokenizer $tokenizer)
    {
        $this->data = (string) $tokenizer->getCurrentToken(1);
    }

    /**
     * @return string
     */
    private function format() : string
    {
        return $this->indent(str_replace('	', '    ', $this->data));
    }
}
