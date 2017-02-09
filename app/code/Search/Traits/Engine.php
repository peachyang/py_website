<?php

namespace Seahinet\Search\Traits;

use Seahinet\Search\Model;

trait Engine
{

    /**
     * @return Model\EngineInterface
     */
    public function getSearchEngineHandler($name = null)
    {
        $config = $this->getContainer()->get('config');
        $engine = '\\Seahinet\\Search\\Model\\' .
                (is_null($name) ? ($config['adapter']['search_engine'] ?? $this->getContainer()->get('dbAdapter')->getPlatform()->getName()) : $name);
        $engine = class_exists($engine) ? new $engine : new Model\NoEngine;
        return new $engine;
    }

}
