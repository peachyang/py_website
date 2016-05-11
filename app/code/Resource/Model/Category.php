<?php

namespace Seahinet\Admin\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Lib\Session\Segment;


/**
 * System backend Resource category
 */
class Category extends AbstractModel
{


    protected function construct()
    {
        $this->init('file_Resource_category', 'id', ['id', 'store_id', 'parent_id', 'code']);
    }

   
    
    
    protected function beforeSave()
    {
       
        parent::beforeSave();
    }

}
