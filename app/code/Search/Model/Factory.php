<?php

namespace Seahinet\Search\Model;

use Exception;

class Factory
{

    use \Seahinet\Lib\Traits\Container;

    private static $engine = null;
    private static $engines = [];

    /**
     * @return EngineInterface
     */
    public function getSearchEngineHandler($name = null)
    {
        if (is_null($name)) {
            if (is_null(self::$engine)) {
                $config = $this->getContainer()->get('config');
                $default = $this->getContainer()->get('dbAdapter')->getPlatform()->getName();
                $name = $config['adapter']['search_engine']['adapter'] ?? $default;
                self::$engine = $this->getEngineInstance($name, $default);
            }
            return self::$engine;
        } else {
            if (!isset(self::$engines[$name])) {
                self::$engines[$name] = $this->getEngineInstance($name);
            }
            return self::$engines[$name];
        }
    }

    private function getEngineInstance($name, $default = '')
    {
        try {
            $engine = '\\Seahinet\\Search\\Model\\' . $name;
            $default = '\\Seahinet\\Search\\Model\\' . $default;
            $engine = class_exists($engine) ? new $engine : (class_exists($default) ? new $default : new Model\NoEngine);
        } catch (Exception $e) {
            $this->getContainer()->get('log')->logException($e);
            $engine = new Model\NoEngine;
        }
        return $engine;
    }

}
