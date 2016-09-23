<?php

namespace Seahinet\Resource\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

/**
 * System backend Resource category
 */
class Resource extends AbstractCollection
{

    protected function construct()
    {
        $this->init('resource');
    }

}
