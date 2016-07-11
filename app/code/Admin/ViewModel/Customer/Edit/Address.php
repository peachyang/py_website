<?php

namespace Seahinet\Admin\ViewModel\Customer\Edit;

use Seahinet\Customer\Model\Address as Model;
use Seahinet\Customer\Model\Collection\Address as Collection;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\Eav\Attribute as AttributeModel;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\ViewModel\Template;
use Seahinet\Lib\ViewModel\Template;

class Address extends Template
{

    protected $collection = null;

    public function getInputBox($key, $item)
    {
        if (empty($item['type'])) {
            return '';
        }
        $class = empty($item['view_model']) ? '\\Seahinet\\Lib\\ViewModel\\Template' : $item['view_model'];
        $box = new $class;
        $box->setVariables([
            'key' => $key,
            'item' => $item,
            'parent' => $this
        ]);
        $box->setTemplate('admin/renderer/' . $item['type']);
        return $box;
    }

    public function getCollection()
    {
        if (is_null($this->collection)) {
            if ($id = $this->getQuery('id')) {
                $languageId = Bootstrap::getLanguage()->getId();
                $collection = new Collection($languageId);
                $collection->where(['customer_id' => $id]);
                $this->collection = [];
                foreach ($collection as $address) {
                    $this->collection[] = new Model($languageId, $address);
                }
            }
        }
        return $this->collection;
    }

    public function getElements()
    {
        $languageId = Bootstrap::getLanguage()->getId();
        $attributes = new Attribute;
        $attributes->withLabel($languageId)
                ->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [], 'right')
                ->order('eav_attribute.id')
                ->where(['eav_entity_type.code' => Collection::ENTITY_TYPE]);
        $columns = [
            'id' => [
                'type' => 'hidden'
            ],
            'csrf' => [
                'type' => 'csrf'
            ],
            'customer_id' => [
                'type' => 'hidden',
                'value' => $this->getQuery('id')
            ],
        ];
        foreach ($attributes as $attribute) {
            if ($attribute['id']) {
                $columns[$attribute['code']] = [
                    'label' => $attribute['label'],
                    'type' => $attribute['input'],
                    'class' => $attribute['validation']
                ];
                if (in_array($attribute['input'], ['select', 'radio', 'checkbox', 'multiselect'])) {
                    $columns[$attribute['code']]['options'] = (new AttributeModel($attribute))->getOptions($languageId);
                }
                if ($attribute['is_required']) {
                    $columns[$attribute['code']]['required'] = 'required';
                }
            }
        }
        $columns['is_default'] = [
            'type' => 'radio',
            'label' => 'As Default Address',
            'required' => 'required',
            'options' => [
                1 => 'Yes',
                0 => 'No'
            ]
        ];
        return $columns;
    }

}
