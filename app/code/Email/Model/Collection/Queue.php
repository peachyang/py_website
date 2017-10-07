<?php

namespace Seahinet\Email\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Queue extends AbstractCollection
{

    protected function construct()
    {
        $this->init('email_queue');
    }

}
