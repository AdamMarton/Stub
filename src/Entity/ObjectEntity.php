<?php declare(strict_types=1);

namespace AdamMarton\Stub\Entity;

use AdamMarton\Stub\Entity;
use AdamMarton\Stub\EntityInterface;
use AdamMarton\Stub\Storage;
use AdamMarton\Stub\Tokenizer;
use AdamMarton\Stub\Token\TokenIterator;
use AdamMarton\Stub\Token\Traverse\Criteria;

final class ObjectEntity extends Entity implements EntityInterface
{
    /**
     * @var string
     */
    const DATA_SIGNATURE = 'signature';

    /**
     * @var string
     */
    const DATA_TYPE      = 'type';

    /**
     * @var string
     */
    protected $type      = Storage::S_OBJECT;

    /**
     * @return string
     */
    public function __toString() : string
    {
        $class = [];

        foreach ($this as $source) {
            $class[] = $this->format($source);
        }

        return implode(Tokenizer::LINE_BREAK, $class) . Tokenizer::BRACKET_CLOSE . Tokenizer::LINE_BREAK;
    }

    /**
     * @param  TokenIterator $tokenIterator
     * @return void
     */
    public function add(TokenIterator $tokenIterator)
    {
        $this->data[] = [
            self::DATA_TYPE      => 'header',
            self::DATA_SIGNATURE => $tokenIterator->seekUntil(new Criteria(Tokenizer::BRACKET_OPEN))
        ];
    }

    /**
     * @param  array $source
     * @return string
     */
    public function format(array $source) : string
    {
        list($type, $signature) = array_values($source);
        $formatterMethod        = 'format' . ucfirst((string) $type);

        if (method_exists($this, $formatterMethod)) {
            return call_user_func_array(
                function (...$parameters) use ($formatterMethod) : string {
                    return (string) $this->$formatterMethod(...$parameters);
                },
                [$signature]
            );
        }

        return '';
    }

    /**
     * @param  EntityInterface $entity
     * @return void
     */
    public function addUse(EntityInterface $entity)
    {
        $entity->setIndent(4);

        $this->data[] = [
            self::DATA_TYPE      => 'use',
            self::DATA_SIGNATURE => $entity
        ];
    }

    /**
     * @param  EntityInterface $entity
     * @return string
     */
    private function formatUse(EntityInterface $entity) : string
    {
        try {
            return (string) $entity;
        } catch (\Exception $e) {
            print($e->getMessage());
            return '';
        }
    }

    /**
     * @param  EntityInterface $entity
     * @return void
     */
    public function addDocblock(EntityInterface $entity)
    {
        if (sizeof($this->data)) {
            $lastData = array_pop($this->data);
            
            if (is_array($lastData) && $lastData[self::DATA_TYPE] !== 'docblock') {
                $this->data[] = $lastData;
            }

            $entity->setIndent(4);

            $this->data[] = [
                self::DATA_TYPE      => 'docblock',
                self::DATA_SIGNATURE => $entity
            ];
        }
    }

    /**
     * @param  array $signature
     * @return string
     */
    private function formatHeader(array $signature) : string
    {
        return Tokenizer::LINE_BREAK .
            str_replace(
                [' , ', ' ' . Tokenizer::BRACKET_OPEN],
                [', ', Tokenizer::LINE_BREAK . Tokenizer::BRACKET_OPEN],
                implode(' ', $signature)
            ) . Tokenizer::LINE_BREAK;
    }

    /**
     * @param  EntityInterface $entity
     * @return string
     */
    private function formatDocblock(EntityInterface $entity) : string
    {
        return (string) $entity;
    }

    /**
     * @param  EntityInterface $entity
     * @return void
     */
    public function addProperty(EntityInterface $entity)
    {
        $entity->setIndent(4);

        $this->data[] = [
            self::DATA_TYPE      => 'property',
            self::DATA_SIGNATURE => $entity
        ];
    }

    /**
     * @param  EntityInterface $entity
     * @return string
     */
    protected function formatProperty(EntityInterface $entity) : string
    {
        return (string) $entity;
    }

    /**
     * @param  EntityInterface $entity
     * @return void
     */
    public function addMethod(EntityInterface $entity)
    {
        $entity->setIndent(4);
        $this->data[] = [
            self::DATA_TYPE      => 'method',
            self::DATA_SIGNATURE => $entity
        ];
    }

    /**
     * @param  EntityInterface $entity
     * @return string
     */
    protected function formatMethod(EntityInterface $entity) : string
    {
        return (string) $entity;
    }
}
