<?php

namespace Seahinet\Admin\ViewModel\Customer\Edit;

use Seahinet\Customer\Model\Address as Model;
use Seahinet\Customer\Model\Collection\Address as Collection;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\Eav\Attribute as AttributeModel;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\ViewModel\AbstractViewModel;
use Seahinet\Lib\ViewModel\Template;

class Address extends AbstractViewModel
{

    protected $collection = null;

    public function getInputBox($key, $item)
    {
        $box = new Template;
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
                foreach($collection as $address){
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
        $columns = [];
        foreach ($attributes as $attribute) {
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
        return $columns;
    }

}
