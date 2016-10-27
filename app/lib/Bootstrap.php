<?php

namespace Seahinet\Lib;

use Interop\Container\ContainerInterface;
use Seahinet\Lib\Model\Merchant;
use Seahinet\Lib\Model\Store;
use Seahinet\Lib\Model\Language;
use Symfony\Component\Yaml\Yaml;

/**
 * Bootstrap main system
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
     * @var Merchant 
     */
    private static $merchant = null;

    /**
     * @var Store
     */
    private static $store = null;

    /**
     * @var Language
     */
    private static $language = null;

    /**
     * @var int
     */
    const SHMOP_SIZE = 524288;

    /**
     * Prepare or get container singleton
     * 
     * @param array $config         DI config
     * @return ContainerInterface
     */
    public static function getContainer()
    {
        if (is_null(static::$container)) {
            static::$container = new Container();
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
        date_default_timezone_set($config['global/locale/timezone'] ?: 'UTC');
        $segment = new Session\Segment('core');
        $language = static::getLanguage($server, $segment);
        static::$container['language'] = $language;
        static::$container['translator']->setLocale($language['code']);
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
        static::$eventDispatcher->trigger('route', ['routers' => static::getContainer()->get('config')['route']]);
        static::$eventDispatcher->trigger('render', ['response' => static::getContainer()->get('response')->getData()]);
        static::$eventDispatcher->trigger('respond');
    }

    /**
     * Prepare config from cache
     * 
     * @return Config
     */
    private static function prepareConfig()
    {
        if (extension_loaded('shmop')) {
            $ftok = function_exists('ftok') ? 'ftok' : function($pathname, $proj) {
                $st = @stat($pathname);
                if (!$st) {
                    return -1;
                }
                $key = sprintf("%u", (($st['ino'] & 0xffff) | (($st['dev'] & 0xff) << 16) | (($proj & 0xff) << 24)));
                return $key;
            };
            $shmid = shmop_open($ftok(__FILE__, 'R'), 'c', 0644, self::SHMOP_SIZE);
            $data = @gzdecode(trim(shmop_read($shmid, 0, self::SHMOP_SIZE)));
            $config = $data ? json_decode($data, true) : false;
        } else {
            $adapter = Yaml::parse(file_get_contents(BP . 'app/config/adapter.yml'));
            $cache = Cache::instance($adapter['cache'] ?? ['adapter' => '']);
            $config = $cache->fetch('SYSTEM_CONFIG');
        }
        if (!$config) {
            $config = Config::instance();
            static::getContainer();
            $config->loadFromDB();
            if (isset($shmid)) {
                shmop_write($shmid, gzencode(json_encode($config->getArrayCopy())), 0);
            } else {
                $cache->save('SYSTEM_CONFIG', $config->getArrayCopy());
            }
        } else {
            $config = Config::instance($config);
            static::getContainer();
        }
        if (isset($shmid)) {
            shmop_close($shmid);
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
        if (isset($config['event'])) {
            static::$eventDispatcher = static::getContainer()->get('eventDispatcher');
            foreach ($config['event'] as $name => $events) {
                if (!is_array($events)) {
                    $events = [$events];
                }
                uasort($events, function($a, $b) {
                    if (!isset($a['priority'])) {
                        $a['priority'] = 0;
                    }
                    if (!isset($b['priority'])) {
                        $b['priority'] = 0;
                    }
                    return $a['priority'] <=> $b['priority'];
                });
                foreach ($events as $event) {
                    static::$eventDispatcher->addListener($name, ($event['listener'] ?? $event), $event['priority'] ?? 0);
                }
            }
        }
    }

    public static function getLanguage($server = null, $segment = null)
    {
        if (is_null(static::$language)) {
            if (is_null($server)) {
                $server = $_SERVER;
            }
            if (is_null($segment)) {
                $segment = new Session\Segment('core');
            }
            $code = $segment->get('language') ?:
                    ($_COOKIE['language'] ??
                    ($server['language'] ?? null));
            if (is_string($code)) {
                $language = new Language;
                $language->load($code, 'code');
                if ($language->getId()) {
                    static::$language = $language;
                }
            }
            if (is_null(static::$language)) {
                static::$language = static::getMerchant($server, $segment)->getLanguage();
                $code = static::$language['code'];
            }
            $segment->set('language', $code);
            if (!isset($_COOKIE['language']) || $_COOKIE['language'] !== $code) {
                static::getContainer()->get('response')->withCookie('language', ['value' => $code, 'path' => '/']);
            }
        }
        return static::$language;
    }

    public static function getStore($server = null, $segment = null)
    {
        if (is_null(static::$store)) {
            if (is_null($server)) {
                $server = $_SERVER;
            }
            if (is_null($segment)) {
                $segment = new Session\Segment('core');
            }
            $code = $segment->get('store') ?: (isset($_COOKIE['store']) ?
                    $_COOKIE['store'] : (isset($server['store']) ?: null));
            if (is_string($code)) {
                $store = new Store;
                $store->load($code, 'code');
                if ($store->getId()) {
                    static::$store = $store;
                }
            }
            if (is_null(static::$store)) {
                static::$store = static::getMerchant($server, $segment)->getStore();
                $code = static::$store['code'];
            }
            $segment->set('store', $code);
            if (!isset($_COOKIE['store']) || $_COOKIE['store'] !== $code) {
                static::getContainer()->get('response')->withCookie('store', ['value' => $code, 'path' => '/']);
            }
        }
        return static::$store;
    }

    public static function getMerchant($server = null, $segment = null)
    {
        if (is_null(static::$merchant)) {
            if (is_null($server)) {
                $server = $_SERVER;
            }
            if (is_null($segment)) {
                $segment = new Session\Segment('core');
            }
            if (!is_null(static::$language) && static::$language['merchant_id']) {
                static::$merchant = new Merchant();
                static::$merchant->load(static::$language['merchant_id']);
            } else if (!is_null(static::$store) && static::$store['merchant_id']) {
                static::$merchant = new Merchant();
                static::$merchant->load(static::$store['merchant_id']);
            } else {
                $code = $segment->get('merchant') ?: (isset($server['merchant']) ?: null);
                if (is_string($code)) {
                    $merchant = new Merchant;
                    $merchant->load($code, 'code');
                    if ($merchant->getId()) {
                        static::$merchant = $merchant;
                    }
                }
                if (is_null(static::$merchant)) {
                    static::$merchant = new Merchant;
                    static::$merchant->load(1, 'is_default');
                    $code = static::$merchant['code'];
                }
                $segment->set('merchant', $code);
            }
        }
        return static::$merchant;
    }

}
