<?php

namespace Seahinet\Lib;

use Seahinet\Lib\Stdlib\Singleton;
use Seahinet\Lib\Stdlib\ArrayObject;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;

/**
 * Main configuration of system
 */
final class Config extends ArrayObject implements Singleton
{

    use Traits\Container,
        Traits\ArrayMerge;

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
        $finder->files()->in(BP . 'app')->name('*.yml');
        $parser = new Parser;
        $config = [];
        foreach ($finder as $file) {
            $key = str_replace('.yml', '', $file->getFilename());
            if (!isset($config[$key])) {
                $config[$key] = [];
            }
            $config[$key] = $this->arrayMerge($config[$key], $parser->parse($file->getContents()));
        }
        return $config;
    }

}
