<?php

namespace Seahinet\Lib;

use Interop\Container\ContainerInterface;
use Pimple\Container as PimpleContainer;

class Container extends PimpleContainer implements ContainerInterface
{

    public function get($id)
    {
        $result = $this->offsetGet($id);
        if (is_string($result) && class_exists($result)) {
            $result = new $result($this);
            $this->protected[$id] = $result;
            $this->value[$id] = $result;
        }
        return $result;
    }

    public function has($id)
    {
        return $this->offsetExists($id);
    }

}
