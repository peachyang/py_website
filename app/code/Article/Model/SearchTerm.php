<?php

namespace Seahinet\Article\Model;

use Seahinet\Search\Model\Term;

class SearchTerm extends Term
{

    protected function construct()
    {
        $this->init('product_search_term', 'term', ['term', 'synonym', 'count', 'popularity', 'store_id', 'category_id', 'status']);
    }

    protected function getCollection()
    {
        return new Collection\SearchTerm;
    }

}
