<?php declare(strict_types=1);

namespace Stub;

final class Storage
{
    /**
     * @var string
     */
    const S_DOCBLOCK  = 'docblock';

    /**
     * @var string
     */
    const S_FUNCTION  = 'function';

    /**
     * @var string
     */
    const S_NAMESPACE = 'namespace';

    /**
     * @var string
     */
    const S_STRING    = 'string';

    /**
     * @var string
     */
    const S_USE       = 'use';

    /**
     * @var string
     */
    const S_OBJECT    = 'object';

    /**
     * @var string
     */
    const S_VARIABLE  = 'variable';

    /**
     * @var array
     */
    private $data     = [];

    /**
     * @param  EntityInterface $entity
     * @return void
     */
    public function add(EntityInterface $entity)
    {
        if ((string) $entity === '') {
            return;
        }

        $this->data[] = [
            $entity->type() => $entity
        ];
    }

    /**
     * @param  string $type
     * @return null|EntityInterface
     */
    public function getLast(string $type)
    {
        $columns = array_column($this->data, $type);
        return end($columns);
    }

    /**
     * @return string
     */
    public function format()
    {
        $stub = [];

        foreach ($this->data as $data) {
            $stub[] = current($data);
        }

        return trim(preg_replace(
            [
                "/;\n\s{4}\/\*/s",
                "/\s{4}\n(\;|\]\;)/s",
                "/\{\n\n/s",
                "/\<\?php\n{1,}/s",
                "/namespace\s(.*)\n{3}/s",
                "/\s{4}\}\n\s{4}\/\*/s"
            ],
            [
                ";\n    /*",
                "    $1\n}",
                "{\n",
                "<?php\n\n",
                "namespace $1\n\n",
                "    }\n\n    /*"
            ],
            (string) implode(Tokenizer::LINE_BREAK, $stub)
        )) . "\n";
    }

    /**
     * @return int
     */
    public function length() : int
    {
        return sizeof($this->data);
    }
}
