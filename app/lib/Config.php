<?php

namespace Seahinet\Lib;

use Seahinet\Lib\Stdlib\Singleton;
use Seahinet\Lib\Stdlib\ArrayObject;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Main configuration of system
 */
final class Config extends ArrayObject implements Singleton
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Lib\Traits\DB,
        \Seahinet\Lib\Traits\ArrayMerge;

    protected static $instance = null;
    protected $keys = [];
    protected $cache = [];

    /**
     * A pattern (a regexp, a glob, or a string)
     *
     * @var string
     */
    protected $bannedYml = 'rbac.yml';

    /**
     * @param array|Container $config
     */
    private function __construct($config = [])
    {
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
    public static function instance($config = [])
    {
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
    private function loadFromYaml()
    {
        $finder = new Finder;
        $finder->files()->in(BP . 'app')->name('*.yml');
        if ($this->bannedYml) {
            $finder->notName($this->bannedYml);
        }
        $parser = new Parser;
        $config = [];
        foreach ($finder as $file) {
            $key = str_replace('.yml', '', $file->getFilename());
            if (!isset($config[$key])) {
                $config[$key] = [];
            }
            try {
                $array = $parser->parse($file->getContents());
            } catch (ParseException $e) {
                throw new ParseException($e->getMessage() . ' File: ' . $file->getRealPath());
            }
            if ($array) {
                $config[$key] = $this->arrayMerge($config[$key], $array);
            }
        }
        return $config;
    }

    /**
     * @return array
     */
    public function loadFromDB()
    {
        $tableGateway = $this->getTableGateway('core_config');
        $result = $tableGateway->select()->toArray();
        $config = [];
        foreach ($result as $item) {
            if (!is_null($item['store_id'])) {
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
    private function generateConfig($path, $value)
    {
        if (count($path) > 1) {
            $key = array_shift($path);
            return [$key => $this->generateConfig($path, $value)];
        } else {
            return [$path[0] => $value];
        }
    }

    private function getConfigByScope(array $array, $scope = null)
    {
        if (empty($this->keys)) {
            $this->keys = [
                'l' => 'l' . Bootstrap::getLanguage()->getId(),
                's' => 's' . Bootstrap::getStore()->getId(),
                'm' => 'm' . Bootstrap::getMerchant()->getId()
            ];
        }
        return is_null($scope) ? ($array[$this->keys['s']] ?? ($array[$this->keys['m']] ?? $array)) : ($array[$scope] ?? ($array[$this->keys['m']] ?? $array));
    }

    private function getConfigByPath($path, $config = null, $scope = null)
    {
        if (count($path) > 1) {
            $key = array_shift($path);
            $config = is_null($config) ? $this->offsetGet($key) :
                    (isset($config[$key]) ? $config[$key] : null);
            if (!is_null($config)) {
                return $this->getConfigByPath($path, $config, $scope);
            }
        } else if (isset($config[$path[0]])) {
            return $this->getConfigByScope($config[$path[0]], $scope);
        } else if (strpos($path[0], '[]')) {
            $path[0] = str_replace('[]', '', $path[0]);
            return explode(',', $this->getConfigByPath($path, $config, $scope));
        }
        return null;
    }

    private function getDefaultConfig($path, $config)
    {
        if (count($path) > 1) {
            $key = array_shift($path);
            $config = isset($config[$key]) ? $config[$key] : null;
            if (!is_null($config) && isset($config['children'])) {
                return $this->getDefaultConfig($path, $config['children']);
            }
        } else if (isset($config[$path[0]]['default'])) {
            return $config[$path[0]]['default'];
        }
        return [];
    }

    public function offsetGet($key)
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        } else if (strpos($key, '/')) {
            $path = explode('/', trim($key, '/'));
            if (preg_match('/^[slm]\d+$/', $path[0])) {
                $scope = array_shift($path);
            }
            $result = $this->getConfigByPath($path, null, $scope ?? null);
            if (is_null($result)) {
                $result = $this->getDefaultConfig($path, $this->storage['system']);
            }
            $this->cache[$key] = $result;
            return $result;
        } else {
            return parent::offsetGet($key);
        }
    }

}
