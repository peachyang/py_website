<?php

namespace Seahinet\Admin\ViewModel;

use Seahinet\Lib\Model\AbstractCollection;
use Seahinet\Lib\ViewModel\AbstractViewModel;

class Grid extends AbstractViewModel
{

    protected $uri = null;
    protected $query = null;

    protected function getUri()
    {
        if (is_null($this->uri)) {
            $this->uri = $this->getRequest()->getUri();
        }
        return $this->uri;
    }

    public function getQuery($key = null, $default = '')
    {
        if (is_null($this->query)) {
            $this->query = parent::getQuery();
        }
        return is_null($key) ? $this->query : (isset($this->query[$key]) ? $this->query[$key] : $default);
    }

    public function __construct()
    {
        $this->setTemplate('admin/grid');
    }

    public function getEditUrl($id = null)
    {
        return $this->getAdminUrl($this->getVariable('edit_url') . (is_null($id) ? '' : '?id=' . $id));
    }

    public function getDeleteUrl()
    {
        return $this->getAdminUrl($this->getVariable('delete_url'));
    }

    public function getOrderByUrl($attr)
    {
        $query = $this->getQuery();
        if (isset($query['asc'])) {
            if ($query['asc'] == $attr) {
                unset($query['asc']);
                $query['desc'] = $attr;
            } else {
                $query['asc'] = $attr;
            }
        } else if (isset($query['desc'])) {
            if ($query['desc'] == $attr) {
                unset($query['desc']);
                $query['asc'] = $attr;
            } else {
                $query['desc'] = $attr;
            }
        } else {
            $query['asc'] = $attr;
        }
        return $this->getUri()->withQuery(http_build_query($query))->__toString();
    }

    public function getLimitUrl()
    {
        $query = $this->getQuery();
        unset($query['limit']);
        if (empty($query)) {
            $url = $this->getUri()->withFragment('')->__toString() . '?';
        } else {
            $url = $this->getUri()->withFragment('')->withQuery(http_build_query($query))->__toString() . '&';
        }
        return $url;
    }

    public function getPagerUrl($pager = 1)
    {
        $query = $this->getQuery();
        $query['page'] = $pager;
        return $this->getUri()->withQuery(http_build_query($query))->__toString();
    }

    public function getAllPages()
    {
        $limit = $this->getQuery('limit', 20);
        $collection = clone $this->getVariable('collection');
        $collection->reset('limit');
        $collection->reset('offset');
        $collection->columns(['id']);
        return ceil(count($collection) / $limit);
    }

    public function getCurrentPage()
    {
        return (int) $this->getQuery('page', 1);
    }

    protected function prepareColumns()
    {
        return [];
    }

    protected function prepareCollection(AbstractCollection $collection = null)
    {
        if (is_null($collection)) {
            return null;
        }
        $condition = $this->getQuery();
        $limit = isset($condition['limit']) ? $condition['limit'] : 20;
        if (isset($condition['page'])) {
            $collection->offset(($condition['page'] - 1) * $limit + 1);
            unset($condition['page']);
        }
        $collection->limit((int) $limit);
        unset($condition['limit']);
        if (isset($condition['asc'])) {
            $collection->order($condition['asc'] . ' ASC');
            unset($condition['asc']);
        } else if (isset($condition['desc'])) {
            $collection->order($condition['desc'] . ' DESC');
            unset($condition['desc']);
        }
        if (!empty($condition)) {
            $collection->where($condition);
        }
        return $collection;
    }

    protected function getRendered()
    {
        $this->setVariables([
            'collection' => $this->prepareCollection(),
            'attributes' => $this->prepareColumns()
        ]);
        return parent::getRendered();
    }

}
