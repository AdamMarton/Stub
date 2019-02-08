<?php declare(strict_types=1);

namespace AdamMarton\Stub\Entity;

use AdamMarton\Stub\Entity;
use AdamMarton\Stub\EntityInterface;
use AdamMarton\Stub\Storage;
use AdamMarton\Stub\Tokenizer;

final class StringEntity extends Entity implements EntityInterface
{
    /**
     * @var string
     */
    protected $type = Storage::S_STRING;

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
        $data = trim(str_replace("\n", '', $tokenizer->getCurrentToken(1)));

        switch ($data) {
            case '<?php':
                break;
            case 'define':
                $data = str_replace([',', '.', "\n"], [', ', ' . ', ''], implode('', array_merge([$data], $tokenizer->advanceTo(Tokenizer::SEMICOLON))) . Tokenizer::SEMICOLON);
                break;
            default:
                $data = '';
                break;
        }

        $this->data = $data;
    }

    private function format()
    {
        return $this->data;
    }
}
