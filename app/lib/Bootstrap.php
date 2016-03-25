<?php

namespace Seahinet\Lib;

use Interop\Container\ContainerInterface;

final class Bootstrap
{

    /**
     * @var ContainerInterface
     */
    private static $container = null;

    public static function getContainer()
    {
        if (is_null(static::$container)) {
            static::$container = new Container;
        }
        return static::$container;
    }

    public static function init($server)
    {
        $config = Config::instance();
        static::handleConfig($config);
    }

    public static function run($server)
    {
        if (is_null(static::$container)) {
            static::init($server);
        }
        static::$container->get('eventDispatcher')->dispatch('run');
    }

    private static function getCallable($name)
    {
        if (is_string($name) && is_subclass_of($name, '\Seahinet\Lib\Stdlib\Singleton')) {
            return $name::instance();
        } else {
            return $name;
        }
    }

    private static function handleConfig($config)
    {
        if (isset($config['di'])) {
            $values = [];
            foreach ($config['di'] as $key => $value) {
                $values[$key] = $value;
            }
            static::$container = new Container($values);
        }
        if (isset($config['event'])) {
            $dispatcher = static::$container->get('eventDispatcher');
            foreach ($config['event'] as $name => $event) {
                $dispatcher->addListener($name, (isset($event['listener']) ? $event['listener'] : $event), isset($event['priority']) ? $event['priority'] : 0);
            }
        }
    }

}
