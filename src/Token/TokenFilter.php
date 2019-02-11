<?php declare(strict_types=1);

namespace Stub\Token;

final class TokenFilter
{
    /**
     * @var array
     */
    private $tokens = [];

    /**
     * @param  array $tokens
     */
    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * @return array $filtered
     */
    public function filter() : array
    {
        $filtered   = [];
        $unfiltered = $this->tokens;

        foreach ($unfiltered as $token) {
            if (is_string($token)) {
                $token = [
                    999999,
                    $token
                ];
            }

            $token[1] = $this->sanitize($token[1]);

            if (!empty(trim($token[1])) || is_numeric($token[1])) {
                $filtered[] = array_slice($token, 0, 2);
            }
        }

        return $filtered;
    }

    /**
     * @param  string $token
     * @return string
     */
    private function sanitize(string $token) : string
    {
        return str_replace(["\n", '//'], [''], trim($token));
    }
}
