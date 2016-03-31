<?php

namespace Seahinet\Lib;

use Interop\Container\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * @todo Bootstrap main system
 */
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
     * @param array $config         DI config
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
     * @throws Exception\MissingFileException
     */
    public static function init($server)
    {
        if (!file_exists(BP . 'app/config/adapter.yml')) {
            throw new Exception\MissingFileException(BP . 'app/config/adapter.yml');
        }
        $config = static::prepareConfig();
        static::handleConfig($config);
        $event = static::$eventDispatcher->trigger('route', ['routers' => $config['route']]);
        static::$eventDispatcher->trigger('render', ['response' => $event['response']]);
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
     * Prepare config from cache
     * 
     * @return Config
     */
    private static function prepareConfig()
    {
        $adapter = Yaml::parse(file_get_contents(BP . 'app/config/adapter.yml'));
        $cache = Cache::instance($adapter['cache']);
        $config = $cache->fetch('SYSTEM_CONFIG');
        if (!$config) {
            $config = Config::instance();
            $cache->save('SYSTEM_CONFIG', $config);
        }
        return $config;
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
