<?php

namespace Seahinet\Admin\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Lib\Session\Segment;


/**
 * System backend user
 */
class FileResoucesCategory extends AbstractModel
{


    protected function _construct()
    {
        $this->init('file_resources_category', 'id', ['id', 'merchant_id', 'parent', 'code']);
    }

   
    protected function beforeSave()
    {
       
        parent::beforeSave();
    }

}
