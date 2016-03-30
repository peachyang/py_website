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

    /**
     * Prepare or get container singleton
     * 
     * @param array $config DI config
     * @return ContainerInterface
     */
    public static function getContainer($config = [])
    {
        if (is_null(static::$container)) {
            static::$container = new Container($config);
            static::$container->register(new ServiceProvider);
        }
        return static::$container;
    }

    /**
     * Initialize system veriables
     * 
     * @param array $server
     */
    public static function init($server)
    {
        $config = Config::instance();
        static::handleConfig($config);
        static::$eventDispatcher->trigger('route', ['routers' => $config['route']]);
    }

    /**
     * Run system
     * 
     * @param array $server
     */
    public static function run($server)
    {
        if (is_null(static::$container)) {
            static::init($server);
        }
        static::$eventDispatcher->trigger('respond', ['response' => static::$container->get('response')]);
    }

    /**
     * Handle the main system configuration
     * 
     * @param Config $config
     */
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
