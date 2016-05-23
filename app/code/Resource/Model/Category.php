<?php

namespace Seahinet\Resource\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Lib\Session\Segment;
use Zend\Db\TableGateway\TableGateway;

/**
 * System backend Resource category
 */
class Category extends AbstractModel
{
    protected function construct()
    {
        $this->init('resource_category', 'id', ['id', 'store_id', 'parent_id', 'code']);
    }

    protected function beforeSave()
    {
        $this->beginTransaction();
        parent::beforeSave();
    }
    
    protected function afterSave(){
        parent::afterSave();
        if (isset($this->storage['name'])) {
            $tableGateway = new TableGateway('resource_category_language', $this->getContainer()->get('dbAdapter'));
            foreach ((array) $this->storage['name'] as $language_id => $name) {
                $this->upsert(['name' => $name], ['category_id' => $this->getId(), 'language_id' => $language_id], $tableGateway);
            }
        }
        $this->commit();
    }
}
