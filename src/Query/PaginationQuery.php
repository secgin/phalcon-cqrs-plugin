<?php

namespace YG\Phalcon\Cqrs\Query;

/**
 * @property ?int    $page
 * @property ?int    $limit
 * @property ?string $sort
 */
abstract class PaginationQuery extends Query
{
    protected ?int $page = null;

    protected ?int $limit = null;

    protected ?string $sort = null;

    protected function setPage(int $value): void
    {
        $this->page = $value == 0 ? 1 : $value;
    }

    protected function setLimit(int $value): void
    {
        $this->limit = $value;
    }

    protected function setSort(?string $value): void
    {
        $this->sort = $value;
    }

    public function getSort(): ?string
    {
        if ($this->sort == '')
            return null;

        $sortFields = explode(',', $this->sort);
        foreach ($sortFields as $index => $name)
        {
            $name = trim($name);
            if ($name == '')
                continue;

            if ($name[0] =='-')
                $sortFields[$index] = ltrim($name, '-') . ' desc';
        }
        return join(',', $sortFields);
    }
}