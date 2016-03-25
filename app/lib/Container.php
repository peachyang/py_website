<?php

namespace Seahinet\Lib;

use Interop\Container\ContainerInterface;
use Pimple\Container as PimpleContainer;

class Container extends PimpleContainer implements ContainerInterface
{

    public function get($id)
    {
        return $this->offsetGet($id);
    }

    public function has($id)
    {
        return $this->offsetExists($id);
    }

}
