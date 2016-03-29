<?php

namespace Seahinet\Lib;

use Interop\Container\ContainerInterface;

final class Bootstrap
{

    /**
     * @var ContainerInterface
     */
    private static $container = null;

    /**
     * @var EventDispatcher
     */
    private static $eventDispatcher = null;

    public static function getContainer($config = [])
    {
        if (is_null(static::$container)) {
            static::$container = new Container($config);
            static::$container->register(new ServiceProvider);
        }
        return static::$container;
    }

    public static function init($server)
    {
        $config = Config::instance();
        static::handleConfig($config);
        static::$eventDispatcher->trigger('route', ['routers' => $config['route']]);
    }

    public static function run($server)
    {
        if (is_null(static::$container)) {
            static::init($server);
        }
        static::$eventDispatcher->trigger('respond', ['response' => static::$container->get('response')]);
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
            static::getContainer($config['di']);
        }
        if (isset($config['event'])) {
            static::$eventDispatcher = static::$container->get('eventDispatcher');
            foreach ($config['event'] as $name => $event) {
                static::$eventDispatcher->addListener($name, (isset($event['listener']) ? $event['listener'] : $event), isset($event['priority']) ? $event['priority'] : 0);
            }
        }
    }

}
