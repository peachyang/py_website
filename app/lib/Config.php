<?php

namespace Seahinet\Lib;

use Seahinet\Lib\Stdlib\Singleton;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;
use Zend\Stdlib\ArrayObject;

final class Config extends ArrayObject implements Singleton
{

    private static $instance = null;

    public function __construct($config = [])
    {
        if ($config instanceof Container) {
            $this->setContainer($config);
            $config = [];
        }
        if (empty($config)) {
            $config = $this->loadFromYaml();
        }
        parent::__construct($config);
    }

    public static function instance($config = [])
    {
        if (is_null(static::$instance)) {
            static::$instance = new static($config);
        }
        return static::$instance;
    }

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
