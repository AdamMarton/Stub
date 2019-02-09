<?php declare(strict_types=1);

namespace AdamMarton\Stub\Entity;

use AdamMarton\Stub\Entity;
use AdamMarton\Stub\EntityInterface;
use AdamMarton\Stub\Storage;
use AdamMarton\Stub\Tokenizer;
use AdamMarton\Stub\Token\TokenIterator;
use AdamMarton\Stub\Token\Traverse\Criteria;

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
     * @param  TokenIterator $tokenIterator
     * @return void
     */
    public function add(TokenIterator $tokenIterator)
    {
        $input = $tokenIterator->current();

        switch ($input) {
            case '<?php':
                break;
            case 'define':
                $input = implode('', $tokenIterator->seekUntil(new Criteria(Tokenizer::SEMICOLON)));
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
