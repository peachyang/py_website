<?php

namespace Seahinet\Lib;

use Seahinet\Lib\Indexer\Factory;

class Indexer
{

    use \Seahinet\Lib\Traits\Container;

    protected $config = null;
    protected $handler = [];

    public function __construct($config)
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

    public function getHandler($entityType)
    {
        if (isset($this->handler[$entityType])) {
            $this->handler[$entityType] = Factory::getHandler($this->config, $entityType);
        }
        return $this->handler[$entityType];
    }

    public function delete($entityType, $constraint)
    {
        $this->getHandler($entityType)->delete($constraint);
    }

    public function insert($entityType, $values)
    {
        $this->getHandler($entityType)->insert($values);
    }

    public function reindex($entityType)
    {
        $this->getHandler($entityType)->reindex();
    }

    public function select($entityType, $constraint)
    {
        return $this->getHandler($entityType)->select($constraint);
    }

    public function update($entityType, $values, $constraint)
    {
        $this->getHandler($entityType)->update($values, $constraint);
    }

    public function upsert($entityType, $values, $constraint)
    {
        $this->getHandler($entityType)->upsert($values, $constraint);
    }

}
