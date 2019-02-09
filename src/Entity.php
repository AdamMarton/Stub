<?php declare(strict_types=1);

namespace AdamMarton\Stub;

abstract class Entity
{
    /**
     * @var string
     */
    protected $type   = '';

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var array
     */
    protected $data   = [];

    /**
     * @var int
     */
    protected $indent = 0;

    /**
     * @return string
     */
    public function type() : string
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function current() : array
    {
        return (array) $this->data[$this->position];
    }

    /**
     * @return int
     */
    public function key() : int
    {
        return $this->position;
    }

    /**
     * @return void
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @return bool
     */
    public function valid() : bool
    {
        return isset($this->data[$this->position]);
    }

    /**
     * @return void
     */
    public function setIndent(int $indent)
    {
        $this->indent = $indent;
    }

    /**
     * @return string
     */
    protected function pad() : string
    {
        return str_pad('', $this->indent, ' ');
    }

    /**
     * @param  string|null $string
     * @return string
     */
    protected function indent($string) : string
    {
        if (!is_string($string)) {
            return '';
        }

        $multilineString = explode(Tokenizer::LINE_BREAK, $string);

        if (sizeof($multilineString) > 1) {
            return implode(
                Tokenizer::LINE_BREAK,
                array_map(
                    [$this, 'indent'],
                    $multilineString
                )
            );
        }

        $padding = $this->pad();

        if ($padding) {
            $string = strpos($string, $padding) === false ? $padding . $string : $string;
            if (strpos($string, str_repeat($padding, 2)) === 0) {
                $string = str_replace(str_repeat($padding, 2), $padding, $string);
            }
        }

        return $string;
    }
}
