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
        $input = trim(str_replace(Tokenizer::LINE_BREAK, '', (string) $tokenizer->getCurrentToken(1)));

        switch ($input) {
            case '<?php':
                break;
            case 'define':
                $input = str_replace([',', '.', "\n"], [', ', ' . ', ''], (string) implode('', array_merge([$input], $tokenizer->advanceTo(Tokenizer::SEMICOLON))) . Tokenizer::SEMICOLON);
                break;
            default:
                $input = '';
                break;
        }

        $this->data = $input;
    }

    /**
     * @return string
     */
    private function format() : string
    {
        return $this->data;
    }
}
