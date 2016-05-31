<?php

namespace Seahinet\Lib\Indexer\Handler;

use Exception;
use Seahinet\Lib\Exception\BadIndexerException;
use Seahinet\Lib\Model\Collection\Language;
use MongoDB\Driver\Manager;
use MongoDB\Collection as MongoDBCollection;

class MongoDB extends AbstractHandler
{

    use \Seahinet\Lib\Traits\Container;

    protected $collection = [];
    protected $manager = null;
    protected $db = null;
    protected $entityType = null;

    public function __construct(Manager $manager, $db, $entityType)
    {
        $this->manager = $manager;
        $this->db = $db;
        $this->entityType = $entityType;
    }

    protected function getCollection($languageId)
    {
        if (!isset($this->collection[$languageId])) {
            $this->collection[$languageId] = new MongoDBCollection($this->manager, $this->db, $this->entityType . '_' . $languageId . '_index');
        }
        return $this->collection[$languageId];
    }

    public function delete($languageId, $constraint)
    {
        try {
            return $this->getCollection($languageId)->deleteMany($constraint);
        } catch (Exception $e) {
            throw new BadIndexerException($e->getMessage());
        }
    }

    public function insert($languageId, $values)
    {
        try {
            $values['_id'] = $values['id'];
            return $this->getCollection($languageId)->insertOne($values);
        } catch (Exception $e) {
            throw new BadIndexerException($e->getMessage());
        }
    }

    public function select($languageId, $constraint)
    {
        try {
            return $this->getCollection($languageId)->find($constraint)->toArray();
        } catch (Exception $e) {
            throw new BadIndexerException($e->getMessage());
        }
    }

    public function update($languageId, $values, $constraint)
    {
        try {
            return $this->getCollection($languageId)->updateOne($constraint, $values);
        } catch (Exception $e) {
            throw new BadIndexerException($e->getMessage());
        }
    }

    public function upsert($languageId, $values, $constraint)
    {
        try {
            return $this->getCollection($languageId)->updateOne($constraint, $values, ['upsert' => true]);
        } catch (Exception $e) {
            throw new BadIndexerException($e->getMessage());
        }
    }

    protected function buildData($data)
    {
        foreach ($data as $languageId => $values) {
            $sets = [];
            foreach ($values as $id => $set) {
                $set['_id'] = $id;
                $sets[] = $set;
            }
            $this->getCollection($languageId)->insertMany($sets);
        }
    }

    protected function buildStructure($columns)
    {
        $languages = new Language;
        foreach ($languages as $language) {
            $this->getCollection($language['id'])->drop();
            $indexes = [
                ['key' => ['id' => 1]],
                ['key' => ['store_id' => 1]],
                ['key' => ['increment_id' => 1]],
            ];
            foreach ($columns as $column) {
                if ($column['is_unique']) {
                    $indexes[] = ['key' => [$column['attr'] => 1], 'unique' => true];
                }
            }
            $this->getCollection($language['id'])->createIndexes($indexes);
        }
    }

}
