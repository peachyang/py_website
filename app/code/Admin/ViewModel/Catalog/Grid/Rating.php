<?php

namespace Seahinet\Admin\ViewModel\Catalog\Grid;

use Seahinet\Admin\ViewModel\Grid as PGrid;
use Seahinet\Catalog\Model\Collection\Product\Rating as Collection;
use Seahinet\Catalog\Source\Product;
use Seahinet\Lib\Session\Segment;

class Rating extends PGrid
{

    protected $editUrl = '';
    protected $deleteUrl = '';
    protected $action = ['getEditAction', 'getDeleteAction'];
    protected $translateDomain = 'rating';

    public function getEditAction($item)
    {
        return '<a href="' . $this->getEditUrl() . '?id=' . $item['id'] . '"title="'
                . $this->translate('Edit') . '"><span class="fa fa-fw fa-file-text-o" aria-hidden="true"></span>'
                . '<span class="sr-only">' . $this->translate('Edit') . '</span></a>';
    }

    public function getDeleteAction($item)
    {
         return '<a href="' . $this->getDeleteUrl() . '" data-method="delete" data-params="id=' . $item['id'] .
                '&csrf=' . $this->getCsrfKey() . '" title="' . $this->translate('Delete') .
                '"><span class="fa fa-fw fa-remove" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Delete') . '</span></a>';
    }

    public function getEditUrl()
    {
        if ($this->editUrl === '') {
            return $this->editUrl = $this->getAdminUrl(':ADMIN/catalog_product_rating/edit/');
        }
        return $this->editUrl;
    }

    public function getDeleteUrl()
    {
        if ($this->deleteUrl === '') {
            return $this->deleteUrl = $this->getAdminUrl(':ADMIN/catalog_product_rating/delete/');
        }
        return $this->deleteUrl;
    }

    protected function prepareColumns($columns = [])
    {
        return [
            'id' => [
                'label' => 'ID',
                'type' => 'hidden'
            #'option' => (new Product)->getSourceArray()
            ],
            'type' => [
                'label' => 'Type',
                'type' => 'select',
                'option' => (new Product)->getSourceArray()
            ],
            'title' => [
                'type' => 'text',
                'label' => 'Title'
            ]
        ];
    }

    protected function prepareCollection($collection = null)
    {
        if (is_null($collection)) {
            $collection = new Collection;
            $user = (new Segment('admin'))->get('user');
            if ($user->getStore()) {
                $collection->where(['store_id' => $user->getStore()->getId()]);
            }
        }
        return parent::prepareCollection($collection);
    }

}
