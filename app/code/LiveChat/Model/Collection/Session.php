<?php

namespace Seahinet\LiveChat\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Session extends AbstractCollection
{

    protected function construct()
    {
        $this->init('livechat_session');
    }

}
