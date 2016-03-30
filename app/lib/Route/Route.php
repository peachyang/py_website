<?php

namespace Seahinet\Lib\Route;

/**
 * @todo Customize router
 */
abstract class Route implements RouteInterface
{

    public function serialize()
    {
        return serialize(get_object_vars($this));
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public static function __set_state()
    {
        return new static;
    }

}
