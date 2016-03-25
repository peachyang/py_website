<?php

namespace Seahinet\Lib;

use Zend\Db\Adapter\Adapter;

class DBAdapter
{

    /**
     * @param array $config
     * @return Adapter
     */
    public function __invoke(array $config = [])
    {
        if (empty($config)) {
            $config = $this->getContainer()->get('config')['adapter']['db'];
        }
        return new Adapter($config['db']);
    }

}
