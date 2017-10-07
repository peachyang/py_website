<?php

namespace Seahinet\Admin\ViewModel\Eav;

use Seahinet\Admin\ViewModel\Grid as PGrid;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\Eav\Attribute as AttributeModel;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Source\Store;

abstract class Grid extends PGrid
{

    protected function prepareColumns($columns = [])
    {
        $attributes = new Attribute;
        $languageId = Bootstrap::getLanguage()->getId();
        $collection = $this->getVariable('collection');
        $attributes->withLabel($languageId)
                ->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [], 'right')
                ->where(['eav_entity_type.code' => $collection::ENTITY_TYPE])
                ->where('(filterable=1 OR sortable=1)')
                ->order('eav_attribute.id');
        $user = (new Segment('admin'))->get('user');
        if (empty($columns)) {
            $columns = [
                'id' => [
                    'label' => 'ID',
                ],
                'store_id' => ($user->getStore() ? [
            'type' => 'hidden',
            'value' => $user->getStore()->getId(),
            'use4sort' => false,
            'use4filter' => false
                ] : [
            'type' => 'select',
            'options' => (new Store)->getSourceArray(),
            'label' => 'Store'
                ])
            ];
        }
        foreach ($attributes as $attribute) {
            if (!isset($columns[$attribute['code']])) {
                $columns[$attribute['code']] = [
                    'label' => $attribute['label'],
                    'type' => $attribute['input'],
                    'class' => $attribute['validation'],
                    'view_model' => $attribute['view_model'],
                    'use4sort' => $attribute['sortable'],
                    'use4filter' => $attribute['filterable']
                ];
            }
            if (in_array($attribute['input'], ['select', 'radio', 'checkbox', 'multiselect'])) {
                $columns[$attribute['code']]['options'] = (new AttributeModel($attribute))->getOptions($languageId);
            }
        }
        $columns['status'] = [
            'type' => 'select',
            'label' => 'Status',
            'options' => [
                1 => 'Enabled',
                0 => 'Disabled'
            ]
        ];
        return $columns;
    }

    protected function prepareCollection($collection = null)
    {
        $user = (new Segment('admin'))->get('user');
        if ($user->getStore()) {
            $collection->where(['store_id' => $user->getStore()->getId()]);
        }
        return parent::prepareCollection($collection);
    }

}
