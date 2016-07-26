<?php

namespace Seahinet\Resource\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;
use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Sql\Predicate\In;

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
