<?php

namespace Seahinet\Catalog\ViewModel\Category;

use Seahinet\Catalog\Source\Sortable;

class SortBy extends Toolbar
{

    protected $attributes = null;

    public function getAttributes()
    {
        if (is_null($this->attributes)) {
            $this->attributes = (new Sortable)->getSourceArray();
        }
        return $this->attributes;
    }

    public function getSorters()
    {
        $result = [];
        $category = $this->getVariable('category');
        if ($category && $category['sortable']) {
            foreach ((array) $category['sortable'] as $key) {
                $result[$key] = $this->getAttributes()[$key];
            }
        }
        return $result;
    }

    public function getCurrentSorter()
    {
        $query = $this->getQuery();
        return isset($query['asc']) ? $query['asc'] : (isset($query['desc']) ?
                        $query['desc'] : ($this->getVariable('category') ?
                                $this->getVariable('category')['default_sortable'] : ''));
    }

    public function isAscending()
    {
        return (bool) $this->getQuery('desc', true);
    }

    public function getSorterUrl($key)
    {
        $query = $this->getCurrentUri()->getQuery();
        if ($key === $query[$this->isAscending() ? 'asc' : 'desc']) {
            $query[$this->isAscending() ? 'desc' : 'asc'] = $key;
            unset($query[$this->isAscending() ? 'asc' : 'desc']);
        } else {
            $query['asc'] = $key;
            unset($query['desc']);
        }
        return $this->getCurrentUri()->withQuery($query);
    }

}
