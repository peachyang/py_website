<?php

namespace Seahinet\Lib;

use Seahinet\Lib\Indexer\Factory;

class Indexer implements Stdlib\Singleton
{

    use \Seahinet\Lib\Traits\Container;

    protected $config = null;
    protected $handler = [];
    protected static $instance = null;

    private function __construct($config)
    {
        if ($config instanceof Container) {
            $this->setContainer($config);
            $config = [];
        }
        if (empty($config)) {
            $adapterObject = $this->getContainer()->get('config')['adapter'];
            $config = isset($adapterObject['indexer']) ? $adapterObject['indexer'] : [];
        }
        $this->config = $config;
    }

    public static function instance($config = [])
    {
        if (is_null(static::$instance)) {
            static::$instance = new static($config);
        }
        return static::$instance;
    }

    public function getHandler($entityType)
    {
        if (!isset($this->handler[$entityType])) {
            $this->handler[$entityType] = Factory::getHandler($this->config, $entityType);
        }
        return $this->handler[$entityType];
    }

    public function delete($entityType, $languageId, $constraint)
    {
        $this->getHandler($entityType)->delete($languageId, $constraint);
    }

    public function insert($entityType, $languageId, $values)
    {
        $this->getHandler($entityType)->insert($languageId, $values);
    }

    public function reindex($entityType)
    {
        $this->getHandler($entityType)->reindex();
    }

    public function select($entityType, $languageId, $constraint)
    {
        return $this->getHandler($entityType)->select($languageId, $constraint);
    }

    public function update($entityType, $languageId, $values, $constraint)
    {
        $this->getHandler($entityType)->update($languageId, $values, $constraint);
    }

    public function upsert($entityType, $languageId, $values, $constraint)
    {
        $this->getHandler($entityType)->upsert($languageId, $values, $constraint);
    }

}
