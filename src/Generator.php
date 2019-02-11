<?php

namespace Stub;

final class Generator
{
    /**
     * @var array
     */
    protected $stub = [];

    /**
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * @return void
     */
    public function push(string $stub)
    {
        $this->stub[] = $stub;
    }

    /**
     * @return void
     */
    public function format()
    {
        foreach ($this->stub as $stub) {
            yield $stub;
        }
    }
}
