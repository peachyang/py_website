<?php

namespace Seahinet\Lib;

use Zend\Db\Adapter\Adapter;

/**
 * Database adapter factory
 */
class DBAdapter
{

    use Traits\Container;
    
    /**
     * @param array $config
     * @return Adapter
     */
    public function __invoke($config = [])
    {
        if($config instanceof Container){
            $this->setContainer($config);
            $config = [];
        }
        if (empty($config)) {
            $config = $this->getContainer()->get('config')['adapter']['db'];
        }
        return new Adapter($config);
    }

}
