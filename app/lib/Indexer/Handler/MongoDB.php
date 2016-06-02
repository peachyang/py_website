<?php

namespace Seahinet\Lib\Indexer\Handler;

use Exception;
use Seahinet\Lib\Exception\BadIndexerException;
use Seahinet\Lib\Model\Collection\Language;
use MongoDB\Driver\Manager;
use MongoDB\Collection as MongoDBCollection;
use Zend\Db\Sql\Select;

/**
 * MongoDB indexer handler
 */
class MongoDB extends AbstractHandler
{

    use \Seahinet\Lib\Traits\Container;

    /**
     * @var array
     */
    protected $collection = [];

    /**
     * @var Manager 
     */
    protected $manager = null;

    /**
     * @var string
     */
    protected $db = null;

    /**
     * @var string
     */
    protected $entityType = null;

    public function __construct(Manager $manager, $db, $entityType)
    {
        $this->manager = $manager;
        $this->db = $db;
        $this->entityType = $entityType;
    }

    /**
     * Get collection based on language id
     * 
     * @param int $languageId
     * @return MongoDBCollection
     */
    protected function getCollection($languageId)
    {
        if (!isset($this->collection[$languageId])) {
            $this->collection[$languageId] = new MongoDBCollection($this->manager, $this->db, $this->entityType . '_' . $languageId . '_index');
        }
        return $this->collection[$languageId];
    }

    /**
     * {@inhertdoc}
     */
    public function delete($languageId, $constraint = [], array $options = [])
    {
        try {
            return $this->getCollection($languageId)->deleteMany($constraint, $options);
        } catch (Exception $e) {
            throw new BadIndexerException($e->getMessage());
        }
    }

    /**
     * {@inhertdoc}
     */
    public function insert($languageId, $values, array $options = [])
    {
        try {
            $values['_id'] = $values['id'];
            return $this->getCollection($languageId)->insertOne($values, $options);
        } catch (Exception $e) {
            throw new BadIndexerException($e->getMessage());
        }
    }

    /**
     * Generate option based on SQL
     * 
     * @param Select $select
     * @return array
     */
    protected function getOptionFromSelect(Select $select)
    {
        $options = [];
        $states = $select->getRawState();
        if ($limit = $states['limit']) {
            $options['limit'] = (int) $limit;
        }
        if ($skip = $states['offset']) {
            $options['skip'] = (int) $skip;
        }
        if ($sort = $states['order']) {
            $options['sort'] = [];
            foreach ($sort as $key => $value) {
                $parts = is_int($key) ? explode(' ', trim($value)) : [$key, $value];
                $options['sort'][$parts[0]] = !isset($parts[1]) || strcasecmp($parts[1], 'desc') ? 1 : -1;
            }
        }
        return $options;
    }

    /**
     * Generate filter based on SQL
     * 
     * @param Select $select
     * @return array
     */
    protected function getFilterFromSelect(Select $select)
    {
        $predicates = $select->getRawState('where')->getExpressionData();
        $parts = [];
        $hasOr = false;
        for ($i = 0; $i < count($predicates); $i++) {
            $predicate = $predicates[$i];
            if (is_array($predicate)) {
                $expression = preg_replace('#^(?:\s*\%s\s+)([^\s]+)(?:\s+\%s\s*)$#', '$1', $predicate[0]);
                $parts[$i] = [$predicate[1][0] => [str_replace(['>=', '<=', '<>', '!=', '>', '<', '='], ['$gte', '$lte', '$ne', '$ne', '$gt', '$lt', '$eq'], $expression) => $predicate[1][1]]];
            }
        }
        $handleAnd = function($a, $b) {
            if (isset($a['$and'])) {
                $a = $a['$and'];
            } else {
                $a = [$a];
            }
            if (isset($b['$and'])) {
                $b = $b['$and'];
            } else {
                $b = [$b];
            }
            return array_merge($a, $b);
        };
        for ($i = 0; $i < count($predicates) - 1; $i++) {
            $predicate = $predicates[$i];
            if (is_string($predicate)) {
                if (trim($predicate) === 'OR') {
                    $hasOr = true;
                } else {
                    for ($j = $i - 1; $j > 0; $j--) {
                        if (isset($parts[$j])) {
                            break;
                        }
                    }
                    for ($k = $i + 1; $k < count($predicates); $k++) {
                        if (isset($parts[$k])) {
                            break;
                        }
                    }
                    $parts[$k] = ['$and' => $handleAnd($parts[$j], $parts[$k])];
                    unset($parts[$j]);
                }
            }
        }
        if ($hasOr) {
            $parts = ['$or' => array_values($parts)];
        } else if (!empty($parts)) {
            $parts = array_values($parts)[0];
        }
        return $parts;
    }

    /**
     * {@inhertdoc}
     */
    public function select($languageId, $constraint = [], array $options = [])
    {
        try {
            if ($constraint instanceof Select) {
                $options += $this->getOptionFromSelect($constraint);
                $constraint = $this->getFilterFromSelect($constraint);
            }
            return $this->getCollection($languageId)->find($constraint, $options)->toArray();
        } catch (Exception $e) {
            throw new BadIndexerException($e->getMessage());
        }
    }

    /**
     * {@inhertdoc}
     */
    public function update($languageId, $values, $constraint = [], array $options = [])
    {
        try {
            return $this->getCollection($languageId)->updateOne($constraint, ['$set' => $values], $options);
        } catch (Exception $e) {
            throw new BadIndexerException($e->getMessage());
        }
    }

    /**
     * {@inhertdoc}
     */
    public function upsert($languageId, $values, $constraint = [], array $options = [])
    {
        try {
            return $this->getCollection($languageId)->updateOne($constraint, ['$set' => $values], ['upsert' => true] + $options);
        } catch (Exception $e) {
            throw new BadIndexerException($e->getMessage());
        }
    }

    /**
     * {@inhertdoc}
     */
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

    /**
     * {@inhertdoc}
     */
    protected function buildStructure($columns, $keys)
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
