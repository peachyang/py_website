<?php

namespace Seahinet\Payment\Model;

abstract class AbstractMethod
{

    use \Seahinet\Lib\Traits\Container;

    abstract public function isValid();

}
