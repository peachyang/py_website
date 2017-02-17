<?php

namespace Seahinet\Catalog\Model\Collection;

use Seahinet\Search\Model\Collection\Term;

class SearchTerm extends Term
{
    
    protected function construct()
    {
        $this->init('product_search_term');
    }

}
