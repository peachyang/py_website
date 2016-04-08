<?php

namespace Seahinet\Lib;

use Pimple\Container as PimpleContainer;
use Pimple\ServiceProviderInterface;
use Seahinet\Lib\Http\Request;
use Seahinet\Lib\Http\Response;
use Zend\Db\Adapter\Adapter;

/**
 * Pimple service provider interface.
 */
class ServiceProvider implements ServiceProviderInterface
{

    /**
     * @param PimpleContainer $container
     */
    public function register(PimpleContainer $container)
    {
        $config = Config::instance($container);
        if (!$container->has('config')) {
            $container['config'] = $config;
        }
        if (!$container->has('cache')) {
            $container['cache'] = Cache::instance(isset($config['adapter']['cache']) ? $config['adapter']['cache'] : $container);
        }
        if (!$container->has('eventDispatcher')) {
            $container['eventDispatcher'] = EventDispatcher::instance();
        }
        if (!$container->has('layout')) {
            $container['layout'] = Layout::instance($config['layout']);
        }
        if (!$container->has('request') && isset($_SERVER['REQUEST_METHOD'])) {
            $request = new Request;
            $container['request'] = $request;
        }
        if (!$container->has('response') && isset($_SERVER['REQUEST_METHOD'])) {
            $response = new Response;
            $response->withStatus(200)
                    ->withHeader('Content-Type', 'text/html; charset=UTF-8');
            $container['response'] = $response;
        }
        if (!$container->has('session')) {
            $container['session'] = Session::instance(isset($config['adapter']['session']) ? $config['adapter']['session'] : $container);
        }
        if (!$container->has('translator')) {
            $container['translator'] = Translator::instance($config['locale']? : $container);
        }
        if (!$container->has('dbAdapter')) {
            $container['dbAdapter'] = new Adapter($config['adapter']['db']);
        }
        if (!$container->has('log')) {
            $container['log'] = function($container) {
                return new Log($container);
            };
        }
        if (!$container->has('mailer')) {
            $container['mailer'] = function($container) {
                return new Mailer($container);
            };
        }
    }

}
