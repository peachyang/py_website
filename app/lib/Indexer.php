<?php

namespace Seahinet\Lib;

use Seahinet\Lib\Indexer\Factory;

/**
 * Indexer manager
 */
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
            $config = $adapterObject['indexer'] ?? [];
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

    /**
     * Get handler based on entity type
     * 
     * @param string $entityType
     * @return Indexer\Handler\AbstractHandler
     */
    public function getHandler($entityType)
    {
        if (!isset($this->handler[$entityType])) {
            $this->handler[$entityType] = Factory::getHandler($this->config, $entityType);
        }
        return $this->handler[$entityType];
    }

    /**
     * Delete data from indexer
     * 
     * @param string $entityType
     * @param int $languageId
     * @param array $constraint
     */
    public function delete($entityType, $languageId, $constraint = [])
    {
        $this->getHandler($entityType)->delete($languageId, $constraint);
    }

    /**
     * Insert data into indexer
     * 
     * @param string $entityType
     * @param int $languageId
     * @param array $values
     */
    public function insert($entityType, $languageId, $values)
    {
        $this->getHandler($entityType)->insert($languageId, $values);
    }

    /**
     * Reindex indexer
     * 
     * @param string $entityType
     */
    public function reindex($entityType)
    {
        $this->getHandler($entityType)->reindex();
    }

    /**
     * Select data from indexer
     * 
     * @param string $entityType
     * @param int $languageId
     * @param array $constraint
     * @return array
     */
    public function select($entityType, $languageId, $constraint = [])
    {
        return $this->getHandler($entityType)->select($languageId, $constraint);
    }

    /**
     * Update data of indexer
     * 
     * @param string $entityType
     * @param int $languageId
     * @param array $values
     * @param array $constraint
     */
    public function update($entityType, $languageId, $values, $constraint = [])
    {
        $this->getHandler($entityType)->update($languageId, $values, $constraint);
    }

    /**
     * Insert/Update data of indexer
     * 
     * @param string $entityType
     * @param int $languageId
     * @param array $values
     * @param array $constraint
     */
    public function upsert($entityType, $languageId, $values, $constraint = [])
    {
        $this->getHandler($entityType)->upsert($languageId, $values, $constraint);
    }

}
