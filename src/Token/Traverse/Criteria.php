<?php declare(strict_types=1);

namespace Stub\Token\Traverse;

final class Criteria
{
    /**
     * @var array
     */
    private $criteria;

    /**
     * @param  string|array $criteria
     */
    public function __construct($criteria)
    {
        $this->criteria = !is_array($criteria) ? [$criteria] : $criteria;
    }

    /**
     * @return array
     */
    public function get() : array
    {
        return $this->criteria;
    }
}
