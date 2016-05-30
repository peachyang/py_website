<?php

namespace Seahinet\Lib\Model;

abstract class AbstractEAVCollection extends AbstractCollection
{

    protected function init($table)
    {
        parent::init($table);
    }

    public function load($useCache = true)
    {
        parent::load($useCache);
    }

}
