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
        $this->handleConfig($config);
    }

    public static function run($server)
    {
        if (is_null(static::$container)) {
            static::init($server);
        }
        static::$container['eventDispatcher']->dispatch('run');
    }

    private static function getCallable($name)
    {
        if (is_string($name) && class_exists($name)) {
            if (is_callable($name . '::instance')) {
                return $name::instance();
            } else {
                return new $name;
            }
        } else {
            return $name;
        }
    }

    private static function handleConfig($config)
    {
        if (isset($config['di'])) {
            $values = [];
            foreach ($config['di'] as $key => $value) {
                $values[$key] = static::getCallable($value);
            }
            static::$container = new Container($values);
        }
        if (isset($config['event'])) {
            $dispatcher = static::$container->get('eventDispatcher');
            foreach ($config['event'] as $event) {
                $dispatcher->addListener($event['name'], static::getCallable($event['listener']), isset($event['priority']) ? $event['priority'] : 0);
            }
        }
    }

}
