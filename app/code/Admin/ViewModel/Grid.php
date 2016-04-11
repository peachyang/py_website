<?php

namespace Seahinet\Admin\ViewModel;

use Seahinet\Lib\Model\AbstractCollection;
use Seahinet\Lib\ViewModel\AbstractViewModel;

class Grid extends AbstractViewModel
{

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
            $collection->order($condition['desc'] . ' ASC');
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
