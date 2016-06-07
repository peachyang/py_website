<?php

namespace Seahinet\Lib\Indexer\Handler;

use InvalidArgumentException;
use Zend\Db\TableGateway\TableGateway;

abstract class AbstractHandler
{

    /**
     * Reindex indexer
     * 
     * @throws InvalidArgumentException
     */
    public function reindex()
    {
        $adapter = $this->getContainer()->get('dbAdapter');
        $tableGateway = new TableGateway('eav_entity_type', $adapter);
        $select = $tableGateway->getSql()->select();
        $select->join('eav_attribute', 'eav_attribute.type_id=eav_entity_type.id', ['attr' => 'code', 'type', 'is_required', 'default_value', 'is_unique'], 'left')
                ->where(['eav_entity_type.code' => $this->entityType])
                ->order('sort_order asc')
                ->columns(['entity_table', 'value_table_prefix']);
        $result = $tableGateway->selectWith($select)->toArray();
        if (count($result) === 0) {
            throw new InvalidArgumentException('Invalid entity type code: ' . $this->entityType);
        }
        $keys = array_keys($tableGateway->selectWith($select->join($result[0]['entity_table'], $result[0]['entity_table'] . '.type_id=eav_entity_type.id', '*', 'left'))->toArray()[0]);
        $this->buildStructure($result, $keys);
        $select
                ->join($result[0]['value_table_prefix'] . '_int', 'eav_attribute.id=' . $result[0]['value_table_prefix'] . '_int.attribute_id AND ' . $result[0]['entity_table'] . '.id=' . $result[0]['value_table_prefix'] . '_int.entity_id', ['value_int' => 'value', 'language_int' => 'language_id'], 'left')
                ->join($result[0]['value_table_prefix'] . '_varchar', 'eav_attribute.id=' . $result[0]['value_table_prefix'] . '_varchar.attribute_id AND ' . $result[0]['entity_table'] . '.id=' . $result[0]['value_table_prefix'] . '_int.entity_id', ['value_varchar' => 'value', 'language_varchar' => 'language_id'], 'left')
                ->join($result[0]['value_table_prefix'] . '_datetime', 'eav_attribute.id=' . $result[0]['value_table_prefix'] . '_datetime.attribute_id AND ' . $result[0]['entity_table'] . '.id=' . $result[0]['value_table_prefix'] . '_int.entity_id', ['value_datetime' => 'value', 'language_datetime' => 'language_id'], 'left')
                ->join($result[0]['value_table_prefix'] . '_blob', 'eav_attribute.id=' . $result[0]['value_table_prefix'] . '_blob.attribute_id AND ' . $result[0]['entity_table'] . '.id=' . $result[0]['value_table_prefix'] . '_int.entity_id', ['value_blob' => 'value', 'language_blob' => 'language_id'], 'left')
                ->join($result[0]['value_table_prefix'] . '_text', 'eav_attribute.id=' . $result[0]['value_table_prefix'] . '_text.attribute_id AND ' . $result[0]['entity_table'] . '.id=' . $result[0]['value_table_prefix'] . '_int.entity_id', ['value_text' => 'value', 'language_text' => 'language_id'], 'left')
                ->join($result[0]['value_table_prefix'] . '_decimal', 'eav_attribute.id=' . $result[0]['value_table_prefix'] . '_decimal.attribute_id AND ' . $result[0]['entity_table'] . '.id=' . $result[0]['value_table_prefix'] . '_int.entity_id', ['value_decimal' => 'value', 'language_decimal' => 'language_id'], 'left');
        $data = $tableGateway->selectWith($select)->toArray();
        $items = [];
        foreach ($data as $record) {
            if (!isset($record['id']) || !$record['id']) {
                continue;
            }
            $languageId = $record['language_varchar']? : (
                    $record['language_int']? : (
                            $record['language_decimal']? : (
                                    $record['language_text']? : (
                                            $record['language_datetime']? :
                                                    $record['language_blob']
                                            ))));
            if (!isset($items[$languageId])) {
                $items[$languageId] = [];
            }
            if (!isset($items[$languageId][$record['id']])) {
                $items[$languageId][$record['id']] = [
                    'id' => $record['id'],
                    'store_id' => $record['store_id'],
                    'increment_id' => $record['increment_id'],
                    'status' => $record['status'],
                    'created_at' => $record['created_at']
                ];
            }
            if ($record['attr']) {
                $items[$languageId][$record['id']][$record['attr']] = $record['value_varchar']? : (
                        $record['value_int']? : (
                                $record['value_decimal']? : (
                                        $record['value_text']? : (
                                                $record['value_datetime']? :
                                                        $record['value_blob']
                                                ))));
            }
        }
        $this->buildData($items);
    }

    /**
     * Build database structure
     * 
     * @param array $attributes
     * @param array $columns
     */
    abstract protected function buildStructure($attributes, $columns);

    /**
     * Append data to database
     * 
     * @param array $data
     */
    abstract protected function buildData($data);

    /**
     * Select data from indexer
     * 
     * @param int $languageId
     * @param array|\Zend\Db\Sql\Select $constraint
     * @param array $options
     * @throws \Seahinet\Lib\Exception\BadIndexerException
     */
    abstract public function select($languageId, $constraint = [], array $options = []);

    /**
     * Insert data into indexer
     * 
     * @param int $languageId
     * @param array $values
     * @param array $options
     * @throws \Seahinet\Lib\Exception\BadIndexerException
     */
    abstract public function insert($languageId, $values, array $options = []);

    /**
     * Update data of indexer
     * 
     * @param int $languageId
     * @param array $values
     * @param array $constraint
     * @param array $options
     * @throws \Seahinet\Lib\Exception\BadIndexerException
     */
    abstract public function update($languageId, $values, $constraint = [], array $options = []);

    /**
     * Insert/Update data of indexer
     * 
     * @param int $languageId
     * @param array $values
     * @param array $constraint
     * @param array $options
     * @throws \Seahinet\Lib\Exception\BadIndexerException
     */
    abstract public function upsert($languageId, $values, $constraint = [], array $options = []);

    /**
     * Delete data of indexer
     * 
     * @param int $languageId
     * @param array $constraint
     * @param array $options
     * @throws \Seahinet\Lib\Exception\BadIndexerException
     */
    abstract public function delete($languageId, $constraint = [], array $options = []);
}
