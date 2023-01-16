<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use Phalcon\Paginator\Repository;

class PaginationRepository extends Repository
{
    public function getPages(): array
    {
        $currentPage = $this->getCurrent();
        $totalItems = $this->getTotalItems();
        $limit = $this->getLimit();

        $stepCount = 2;
        $totalPage = $totalItems > 0 ? ceil($totalItems / $limit) : 0;

        $startPage = ($currentPage - $stepCount) < 1 ? 1 : $currentPage - $stepCount;
        $endPage = ($currentPage - $stepCount) + ($stepCount * 2);
        if ($endPage > $totalPage)
            $endPage = $totalPage;


        $pages = [];
        if ($startPage > 1)
        {
            $pages[] = 1;
            $pages[] = '...';
        }

        for ($i = $startPage; $i <= $endPage; $i++)
            $pages[] = $i;

        if ($endPage < $totalPage)
        {
            $pages[] = '...';
            $pages[] = $totalPage;
        }

        return $pages;
    }

    public function getShownDataText(): string
    {
        $totalItems = $this->getTotalItems();
        $current = $this->getCurrent();
        $limit = $this->getLimit();

        if ($totalItems == 0)
            return "";

        $returnStr = '';
        if ($totalItems > $limit)
        {
            $returnStr = "Gösterilen ";
            $shownFirst = ($current - 1) * $limit;
            if ($shownFirst == 0)
                $shownFirst = 1;

            $shownLast = $current * $limit;
            if ($shownLast > $totalItems)
                $shownLast = $totalItems;

            $returnStr = $returnStr . $shownFirst . " ve " . $shownLast;
        }

        $returnStr .= ($returnStr != '' ? ', ' : '') . 'Toplam ' . $totalItems . ' kayıt';

        return $returnStr;
    }
}