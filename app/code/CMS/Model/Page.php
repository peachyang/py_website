<?php

namespace Seahinet\CMS\Model;

use Seahinet\Lib\Model\AbstractModel;

class Page extends AbstractModel
{

    public function _construct()
    {
        $this->init('cms_page','id','');
    }

}
