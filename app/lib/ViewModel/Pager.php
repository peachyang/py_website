<?php

namespace Seahinet\Lib\ViewModel;

use Seahinet\Lib\Model\AbstractCollection;

/**
 * Pager of collection
 */
class Pager extends AbstractViewModel
{

    /**
     * @var AbstractCollection 
     */
    protected $collection = null;

    /**
     * @var bool
     */
    protected $showLabel = true;

    public function __construct()
    {
        $this->setTemplate('page/pager');
    }

    /**
     * Set and prepare collection
     * 
     * @param AbstractCollection $collection
     * @return Pager
     */
    public function setCollection(AbstractCollection $collection)
    {
        $this->collection = clone $collection;
        $this->collection->reset('limit')
                ->reset('offset')
                ->columns(['id']);
        $this->collection->load();
        return $this;
    }

    /**
     * Get collection
     * 
     * @return AbstractCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Get items count
     * 
     * @return int
     */
    public function getCount()
    {
        if (is_null($this->count)) {
            $this->count = count($this->getCollection()->getArrayCopy());
        }
        return $this->count;
    }

    /**
     * Get sql limit
     * 
     * @return int
     */
    public function getLimit()
    {
        return $this->getQuery('limit', 20);
    }

    /**
     * Get count of pages
     * 
     * @return int
     */
    public function getAllPages()
    {
        return ceil($this->getCount() / $this->getLimit());
    }

    /**
     * Get current page number
     * 
     * @return int
     */
    public function getCurrentPage()
    {
        return (int) $this->getQuery('page', 1);
    }

    /**
     * Should label shown
     * 
     * @param bool $flag
     * @return bool
     */
    public function showLabel($flag = null)
    {
        if (is_bool($flag)) {
            $this->showLabel = $flag;
        }
        return $this->showLabel;
    }

    /**
     * Get pager url
     * 
     * @param int $pager
     * @return string
     */
    public function getPagerUrl($pager = 1)
    {
        $query = $this->getQuery();
        $query['page'] = $pager;
        return $this->getUri()->withQuery(http_build_query($query))->__toString();
    }

}
