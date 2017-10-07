<?php

namespace Seahinet\Email\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Subscriber extends AbstractCollection
{

    protected function construct()
    {
        $this->init('newsletter_subscriber');
    }

}
