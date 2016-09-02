<?php

namespace Seahinet\Customer\ViewModel;

use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\Model\Collection\Eav\Attribute;

class Edit extends Account
{

    public function getAttribute()
    {
        $collection = new Attribute;
        $collection->withLabel()
                ->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [], 'left')
                ->where(['eav_entity_type.code' => Customer::ENTITY_TYPE])
        ->where->notIn('eav_attribute.code', ['username', 'password']);
        return $collection;
    }

    public function getInputBox($attr)
    {
        $item = $attr->toArray();
        $item['value'] = $this->getCustomer()->offsetGet($item['code']);
        $class = empty($item['view_model']) ? '\\Seahinet\\Lib\\ViewModel\\Template' : $item['view_model'];
        $box = new $class;
        $box->setVariables([
            'key' => $item['code'],
            'item' => $item,
            'parent' => $this
        ]);
        $box->setTemplate('page/renderer/' . $item['input'], false);
        return $box;
    }

}
