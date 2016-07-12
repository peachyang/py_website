<?php

namespace Seahinet\Admin\ViewModel\Catalog\Grid;

use Seahinet\Admin\ViewModel\Grid as PGrid;
use Seahinet\Catalog\Model\Product;
use Seahinet\Lib\Model\Collection\Eav\Attribute as Collection;

class Attribute extends PGrid
{

    protected $editUrl = '';
    protected $deleteUrl = '';
    protected $action = ['getEditAction', 'getDeleteAction'];
    protected $translateDomain = 'eav';

    public function getEditAction($item)
    {
        return '<a href="' . $this->getEditUrl() . '?id=' . $item['id'] . '" title="' . $this->translate('Edit') .
                '"><span class="fa fa-fw fa-file-text-o" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Edit') . '</span></a>';
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
            $this->editUrl = $this->getAdminUrl(':ADMIN/catalog_attribute/edit/');
        }
        return $this->editUrl;
    }

    public function getDeleteUrl()
    {
        if ($this->deleteUrl === '') {
            $this->deleteUrl = $this->getAdminUrl(':ADMIN/catalog_attribute/delete/');
        }
        return $this->deleteUrl;
    }

    protected function prepareColumns()
    {
        return [
            'code' => [
                'label' => 'Code',
                'sortby' => 'eav_attribute:code'
            ],
            'type' => [
                'label' => 'Type',
                'type' => 'select',
                'options' => [
                    'varchar' => 'Charector',
                    'int' => 'Integer',
                    'decimal' => 'Decimal',
                    'text' => 'Text',
                    'datetime' => 'Date/Time'
                ]
            ],
            'searchable' => [
                'label' => 'Searchable',
                'type' => 'select',
                'options' => [
                    1 => 'Yes',
                    0 => 'No'
                ]
            ],
            'filterable' => [
                'label' => 'Filterable',
                'type' => 'select',
                'options' => [
                    1 => 'Yes',
                    0 => 'No'
                ]
            ],
            'sortable' => [
                'label' => 'Sortable',
                'type' => 'select',
                'options' => [
                    1 => 'Yes',
                    0 => 'No'
                ]
            ],
            'comparable' => [
                'label' => 'Comparable',
                'type' => 'select',
                'options' => [
                    1 => 'Yes',
                    0 => 'No'
                ]
            ]
        ];
    }

    protected function prepareCollection($collection = null)
    {
        $collection = new Collection;
        $collection->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [], 'left')
                ->where(['eav_entity_type.code' => Product::ENTITY_TYPE]);
        return parent::prepareCollection($collection);
    }
}
