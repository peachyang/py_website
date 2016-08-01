<?php

namespace Seahinet\Customer\Model;

use Seahinet\Lib\Model\AbstractModel;

class Media extends AbstractModel
{

    protected function construct()
    {
        $this->init('social_media', 'id', ['id', 'label', 'link', 'icon']);
    }

}
