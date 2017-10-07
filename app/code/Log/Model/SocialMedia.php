<?php

namespace Seahinet\Log\Model;

use Seahinet\Lib\Model\AbstractModel;

class SocialMedia extends AbstractModel
{

    protected function construct()
    {
        $this->init('social_media_share', 'id', ['id', 'customer_id', 'media_id', 'product_id']);
    }

}
