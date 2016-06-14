<?php

namespace Seahinet\Oauth\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Attribute extends AbstractCollection
{

    protected function construct()
    {
        $this->init('api_rest_attribute');
    }

}
