<?php

namespace Seahinet\Oauth\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Token extends AbstractCollection
{

    protected function construct()
    {
        $this->init('oauth_token');
    }

}
