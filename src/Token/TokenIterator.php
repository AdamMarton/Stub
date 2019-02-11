<?php declare(strict_types=1);

namespace Stub\Token;

final class TokenIterator implements \Iterator
{
    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var array
     */
    private $tokens   = [];

    /**
     * @param  array $tokens
     */
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * @return string
     */
    public function current() : string
    {
        return (string) $this->tokens[$this->position][1];
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
     * @param  int  $position
     * @return void
     */
    public function reset(int $position)
    {
        $this->position = $position;
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @param  Traverse\Criteria $criteria
     * @return array $tempTokens
     */
    public function seekUntil(Traverse\Criteria $criteria) : array
    {
        $tempTokens = [];
        $stop       = $criteria->get();

        while ($this->valid()) {
            $tempToken    = $this->current();
            $tempTokens[] = $tempToken;

            if (in_array($tempToken, $stop)) {
                return $tempTokens;
            }

            $this->next();
        }

        return $tempTokens;
    }

    /**
     * @return int
     */
    public function type() : int
    {
        return (int) $this->tokens[$this->position][0];
    }

    /**
     * @return bool
     */
    public function valid() : bool
    {
        return isset($this->tokens[$this->position]);
    }
}
