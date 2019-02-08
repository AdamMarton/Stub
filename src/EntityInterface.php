<?php declare(strict_types=1);

namespace AdamMarton\Stub;

interface EntityInterface
{
    public function __toString() : string;
    public function type() : string;
    public function setIndent(int $indent);
}
