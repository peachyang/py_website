<?php

namespace Seahinet\Lib;

use Pimple\Container as PimpleContainer;
use Pimple\ServiceProviderInterface;
use Seahinet\Lib\Http\Request;
use Seahinet\Lib\Http\Response;

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

        if (!isset($container['request'])) {
            $container['request'] = function ($container) {
                return new Request;
            };
        }

        if (!isset($container['response'])) {
            $container['response'] = function ($container) {
                $response = new Response;
                $response->withStatus(200);
                $response->withHeader('Content-Type', 'text/html; charset=UTF-8');
                return $response;
            };
        }
    }

}
