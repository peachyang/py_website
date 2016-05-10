<?php

namespace Seahinet\Admin\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Lib\Session\Segment;


/**
 * System backend Resources category
 */
class Category extends AbstractModel
{


    protected function construct()
    {
        $this->init('file_resources_category', 'id', ['id', 'store_id', 'parent_id', 'code']);
    }

   
    
    
    protected function beforeSave()
    {
       
        parent::beforeSave();
    }

}
