<?php

namespace Seahinet\Lib\Traits;

use Interop\Container\ContainerInterface;
use Seahinet\Lib\Bootstrap;

trait Conteiner
{

    /**
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        if (is_null($this->container)) {
            $this->container = Bootstrap::getContainer();
        }
        return $this->container;
    }

    /**
     * @param ContainerInterface $container
     * @return self
     */
    protected function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }

}
