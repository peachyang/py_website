<?php

namespace Seahinet\Lib;

use Seahinet\Lib\Stdlib\Singleton;
use Seahinet\Lib\Stdlib\ArrayObject;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;

/**
 * Main configuration of system
 */
final class Config extends ArrayObject implements Singleton {

    use Traits\DB,
        Traits\ArrayMerge;

    protected static $instance = null;

    /**
     * @param array|Container $config
     */
    private function __construct($config = []) {
        if ($config instanceof Container) {
            $this->setContainer($config);
            $config = [];
        }
        if (empty($config)) {
            $config = $this->loadFromYaml();
        }
        $this->storage = $config;
    }

    /**
     * @param array $config
     * @return Config
     */
    public static function instance($config = []) {
        if (is_null(static::$instance)) {
            if ($config instanceof Config) {
                static::$instance = $config;
            } else {
                static::$instance = new static($config);
            }
        }
        return static::$instance;
    }

    /**
     * @return array
     */
    private function loadFromYaml() {
        $finder = new Finder;
        $finder->files()->in(BP . 'app')->name('*.yml');
        $parser = new Parser;
        $config = [];
        foreach ($finder as $file) {
            $key = str_replace('.yml', '', $file->getFilename());
            if (!isset($config[$key])) {
                $config[$key] = [];
            }
            $array = $parser->parse($file->getContents());
            if ($array) {
                $config[$key] = $this->arrayMerge($config[$key], $array);
            }
        }
        return $config;
    }

    /**
     * @return array
     */
    public function loadFromDB() {
        $tableGateway = $this->getTableGateway('core_config');
        $result = $tableGateway->select()->toArray();
        $config = [];
        foreach ($result as $item) {
            if (!is_null($item['language_id'])) {
                $value['l' . $item['language_id']] = $item['value'];
            } else if (!is_null($item['store_id'])) {
                $value['s' . $item['store_id']] = $item['value'];
            } else {
                $value['m' . $item['merchant_id']] = $item['value'];
            }
            $config = $this->arrayMerge($config, $this->generateConfig(explode('/', $item['path']), $value));
        }
        $this->storage = $this->arrayMerge($this->storage, $config);
        return $config;
    }

    /**
     * Generate path array
     * 
     * @param string|array $path
     * @return array
     */
    private function generateConfig($path, $value) {
        if (count($path) > 1) {
            $key = array_shift($path);
            return [$key => $this->generateConfig($path, $value)];
        } else {
            return [$path[0] => $value];
        }
    }

    private function getConfigByPath($path, $config = null) {
        if (count($path) > 1) {
            $key = array_shift($path);
            $config = is_null($config) ? $this->offsetGet($key) :
                    (isset($config[$key]) ? $config[$key] : null);
            if (!is_null($config)) {
                return $this->getConfigByPath($path, $config);
            }
        } else if (isset($config[$path[0]])) {
            $result = $config[$path[0]];
            return isset($result['l' . Bootstrap::getLanguage()->getId()]) ?
                    $result['l' . Bootstrap::getLanguage()->getId()] :
                    (isset($result['s' . Bootstrap::getStore()->getId()]) ?
                            $result['s' . Bootstrap::getStore()->getId()] :
                            (isset($result['m' . Bootstrap::getMerchant()->getId()]) ?
                                    $result['m' . Bootstrap::getMerchant()->getId()] :
                                    $result));
        }
        return null;
    }

    public function offsetGet($key) {
        if (strpos($key, '/')) {
            return $this->getConfigByPath(explode('/', trim($key, '/')));
        } else {
            return parent::offsetGet($key);
        }
    }

}
