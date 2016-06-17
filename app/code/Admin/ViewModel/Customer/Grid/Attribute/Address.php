<?php

namespace Seahinet\Admin\ViewModel\Customer\Grid\Attribute;

use Seahinet\Admin\ViewModel\Grid as PGrid;
use Seahinet\Customer\Model\Address as Model;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\Collection\Eav\Attribute as Collection;

class Address extends PGrid
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
            $this->editUrl = $this->getAdminUrl(':ADMIN/customer_address/edit/');
        }
        return $this->editUrl;
    }

    public function getDeleteUrl()
    {
        if ($this->deleteUrl === '') {
            $this->deleteUrl = $this->getAdminUrl(':ADMIN/customer_address/delete/');
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
            'label' => [
                'label' => 'Label'
            ],
            'type' => [
                'label' => 'Type',
                'type' => 'select',
                'options' => [
                    'varchar' => 'Charector',
                    'int' => 'Integer',
                    'decimal' => 'Decimal',
                    'text' => 'Text',
                    'blob' => 'Binary',
                    'datetime' => 'Date/Time'
                ]
            ]
        ];
    }

    protected function prepareCollection($collection = null)
    {
        $collection = new Collection;
        $collection->withLabel(Bootstrap::getLanguage()->getId())
                ->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [], 'left')
                ->where(['eav_entity_type.code' => Model::ENTITY_TYPE]);
        return parent::prepareCollection($collection);
    }

}
