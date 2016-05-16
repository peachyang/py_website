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
        if(isset($this->storage['language_id'])){
            $tableGateway = new TableGateway('resource_category_language', $this->getContainer()->get('dbAdapter'));
            foreach ((array) $this->storage['language_id'] as $k => $v) {
                $this->upsert(['name' => $this->storage['name_'.$v]], ['category_id' => $this->getId(), 'language_id' => $v], $tableGateway);
                
            }
        }
        $this->commit();
    }
}
