<?php

namespace Seahinet\Lib;

use Seahinet\Lib\Stdlib\Singleton;
use Seahinet\Lib\Stdlib\ArrayObject;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;

final class Config extends ArrayObject implements Singleton
{

    use Traits\Container;

    private static $instance = null;

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
            static::$instance = new static($config);
        }
        return static::$instance;
    }

    /**
     * @return array
     */
    private function loadFromYaml()
    {
        $finder = new Finder;
        $finder->files()->in('app')->name('*.yml');
        $parser = new Parser;
        $config = [];
        foreach ($finder as $file) {
            $key = str_replace('.yml', '', $file->getFilename());
            if (!isset($config[$key])) {
                $config[$key] = [];
            }
            $config[$key] = array_merge_recursive($config[$key], $parser->parse($file->getContents()));
        }
        return $config;
    }

}
