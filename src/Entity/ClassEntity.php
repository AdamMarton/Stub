<?php declare(strict_types=1);

namespace AdamMarton\Stub\Entity;

use AdamMarton\Stub\Entity;
use AdamMarton\Stub\EntityInterface;
use AdamMarton\Stub\Storage;
use AdamMarton\Stub\Tokenizer;

final class ClassEntity extends Entity implements EntityInterface
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
    protected $type      = Storage::S_CLASS;

    /**
     * @var bool
     */
    protected $namespace = false;

    /**
     * @var bool
     */
    protected $body      = false;

    /**
     * @var bool
     */
    protected $inBody    = false;

    /**
     * @return string
     */
    public function __toString() : string
    {
        $php = [];

        foreach ($this->data as $source) {
            if (is_array($source)) {
                $php[] = $this->format($source);
            }
        }

        return implode(Tokenizer::LINE_BREAK, $php) . Tokenizer::BRACKET_CLOSE . Tokenizer::LINE_BREAK;
    }

    /**
     * @param  Tokenizer $tokenizer
     * @return void
     */
    public function add(Tokenizer $tokenizer)
    {
        $this->data[] = [
            self::DATA_TYPE      => 'header',
            self::DATA_SIGNATURE => array_merge([$tokenizer->getCurrentToken(1)], $tokenizer->advanceTo(Tokenizer::BRACKET_OPEN))
        ];
    }

    /**
     * @return bool
     */
    public function hasBody() : bool
    {
        return $this->body;
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
                function (...$parameters) use ($formatterMethod) {
                    return $this->$formatterMethod(...$parameters);
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
        if (is_array($this->data) && sizeof($this->data)) {
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
     * @param  EntityInterface $entity
     * @return string
     */
    private function formatDocblock(EntityInterface $entity) : string
    {
        return (string) $entity;
    }

    /**
     * @param  array $signature
     * @return string
     */
    private function formatHeader(array $signature) : string
    {
        $this->inBody = true;

        return
            Tokenizer::LINE_BREAK .
            str_replace(' , ', ', ', implode(' ', $signature)) .
            Tokenizer::LINE_BREAK .
            Tokenizer::BRACKET_OPEN;
    }

    /**
     * @param  EntityInterface $entity
     * @return void
     */
    public function addProperty(EntityInterface $entity)
    {
        $entity->setIndent(4);

        $this->body   = true;
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
        $this->body   = true;
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
