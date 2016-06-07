<?php

namespace Seahinet\Lib\Model\Eav\Attribute;

use Seahinet\Lib\Model\AbstractModel;
use Zend\Db\TableGateway\TableGateway;

class Set extends AbstractModel
{

    protected function construct()
    {
        $this->init('eav_attribute_set', 'id', ['id', 'type_id', 'name']);
    }

    protected function beforeSave()
    {
        $this->beginTransaction();
        parent::beforeSave();
    }

    protected function afterSave()
    {
        parent::afterSave();
        $tableGateway = new TableGateway('eav_entity_attribute', $this->getContainer()->get('dbAdapter'));
        $tableGateway->delete(['attribute_set_id' => $this->getId()]);
        if (!empty($this->storage['attributes'])) {
            foreach ($this->storage['attributes'] as $groupId => $attributes) {
                foreach ($attributes as $sortOrder => $attributeId) {
                    $tableGateway->insert([
                        'attribute_set_id' => $this->getId(),
                        'attribute_group_id' => $groupId,
                        'attribute_id' => $attributeId,
                        'sort_order' => $sortOrder
                    ]);
                }
            }
        }
        $this->flushList('eav_attribute');
        $this->commit();
    }

}
