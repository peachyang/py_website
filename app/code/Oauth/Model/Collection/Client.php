<?php

namespace Seahinet\Oauth\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Client extends AbstractCollection
{

    protected function construct()
    {
        $this->init('oauth_client');
    }

}
