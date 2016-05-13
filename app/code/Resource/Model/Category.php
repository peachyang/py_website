<?php

namespace Seahinet\Resource\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Lib\Session\Segment;


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
       
        parent::beforeSave();
    }
    
    protected function afterSave(){
    
    }
    

}
