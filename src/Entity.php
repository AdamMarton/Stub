<?php declare(strict_types=1);

namespace AdamMarton\Stub;

abstract class Entity
{
    /**
     * @var string
     */
    protected $type   = '';

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var int
     */
    protected $indent = 0;

    public function type() : string
    {
        return $this->type;
    }

    public function setIndent(int $indent)
    {
        $this->indent = $indent;
    }

    protected function pad()
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
