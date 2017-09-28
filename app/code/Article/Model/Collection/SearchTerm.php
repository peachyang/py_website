<?php

namespace Seahinet\Article\Model\Collection;

use Seahinet\Search\Model\Collection\Term;

class SearchTerm extends Term
{
    
    protected function construct()
    {
        $this->init('article_search_term');
    }

}
