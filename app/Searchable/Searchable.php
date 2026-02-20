<?php

namespace App\Searchable;

interface Searchable
{
    public function getSearchResult(): SearchResult;
}
