<?php

namespace Seahinet\Admin\Controller\Customer;

use Seahinet\Customer\Model\Address;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\Model\Collection\Eav\Attribute\Set;

class AddressController extends AuthActionController
{

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Customer\\Model\\Address');
    }

    public function saveAction()
    {
        $collection = new Attribute;
        $collection->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [], 'left')
                ->where(['is_required' => 1, 'eav_entity_type.code' => Address::ENTITY_TYPE]);
        $required = [];
        foreach ($collection as $item) {
            $required[] = $item['code'];
        }
        return $this->doSave('\\Seahinet\\Customer\\Model\\Address', null, $required, function($model, $data) {
                    $set = new Set;
                    $set->join('eav_entity_type', 'eav_entity_type.id=eav_attribute_set.type_id', [], 'left')
                            ->where(['eav_entity_type.code' => Address::ENTITY_TYPE]);
                    $model->setData([
                        'attribute_set_id' => $set->toArray()[0]['id'],
                        'store_id' => Bootstrap::getStore()->getId()
                    ]);
                });
    }

}
