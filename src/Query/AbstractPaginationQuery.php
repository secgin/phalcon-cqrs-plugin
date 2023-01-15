<?php

namespace YG\Phalcon\Cqrs\Query;

class AbstractPaginationQuery extends AbstractQuery
{
    protected int $page;

    protected int $limit;

    protected ?string $sort;

    public function __construct()
    {
        parent::__construct();

        $this->page = 1;
        $this->limit = 10;
        $this->sort = null;
    }

    protected function setPage(int $value): void
    {
        $this->page = $value == 0 ? 1 : $value;
    }

    public function getPage(): int
    {
        return $this->page == 0 ? 1 : $this->page;
    }

    protected function setLimit(int $value): void
    {
        $this->limit = $value;
    }

    public function getLimit(): int
    {
        return $this->limit == 0 ? 10 : $this->limit;
    }

    protected function setSort(?string $value): void
    {
        $this->sort = $value;
    }

    public function getSort(): ?string
    {
        if ($this->sort == '')
            return null;

        if ($this->sort[0] == '-')
            return substr($this->sort, 1, strlen($this->sort) - 1) . ' desc';

        return $this->sort;
    }
}