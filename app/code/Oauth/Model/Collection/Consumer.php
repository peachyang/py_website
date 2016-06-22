<?php

namespace Seahinet\Oauth\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Consumer extends AbstractCollection
{

    protected function construct()
    {
        $this->init('oauth_consumer');
    }

}
