<?php

namespace Seahinet\Lib;

use Interop\Container\ContainerInterface;
use Pimple\Container as PimpleContainer;

class Container extends PimpleContainer implements ContainerInterface
{

    public function __construct(array $values = [])
    {
        parent::__construct();
        foreach ($values as $method => $objects) {
            if (is_callable([$this, $method])) {
                foreach ($objects as $key => $param) {
                    $value = $this->$method($param);
                    if (!is_null($value)) {
                        $this->offsetSet($key, $value);
                    }
                }
            }
        }
    }

    public function singleton($className)
    {
        if (is_subclass_of($className, '\\Seahinet\\Lib\\Stdlib\\Singleton')) {
            return $className::instance($this);
        } else {
            return null;
        }
    }

    public function abstractFactory($callable)
    {
        return call_user_func($callable, $this);
    }

    public function factory($callable)
    {
        if (is_string($callable)) {
            $callable = new $callable;
        }
        return parent::factory($callable);
    }

    public function service($service)
    {
        return $service;
    }

    public function get($id)
    {
        $result = $this->offsetGet($id);
        if (is_string($result) && class_exists($result)) {
            $result = new $result($this);
        }
        return $result;
    }

    public function has($id)
    {
        return $this->offsetExists($id);
    }

}
