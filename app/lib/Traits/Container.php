<?php

namespace Seahinet\Lib\Traits;

use Interop\Container\ContainerInterface;
use Seahinet\Lib\Bootstrap;

/**
 * Get/Set DI Container
 */
trait Container
{

    /**
     * @var ContainerInterface
     */
    protected static $container = null;

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        if (is_null(self::$container)) {
            self::$container = Bootstrap::getContainer();
        }
        return self::$container;
    }

    /**
     * @param ContainerInterface $container
     * @return self
     */
    protected function setContainer(ContainerInterface $container)
    {
        self::$container = $container;
        return $this;
    }

}
