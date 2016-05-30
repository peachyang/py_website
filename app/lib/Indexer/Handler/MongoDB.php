<?php

namespace Seahinet\Lib\Indexer\Handler;

use MongoDB\Collection as MongoDBCollection;

class MongoDB implements HandlerInterface
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Lib\Traits\DB;

    protected $collection = null;

    public function __construct(MongoDBCollection $collection)
    {
        $this->collection = $collection;
    }

    public function delete($constraint)
    {
        $this->collection->deleteMany($constraint);
    }

    public function insert($values)
    {
        $this->collection->insertOne($values);
    }

    public function reindex()
    {
        $this->collection->deleteMany('1');
        $adapter = $this->getContainer()->get('dbAdapter');
        $tableGateway = new TableGateway('eav_entity_type', $adapter);
        $select = $tableGateway->getSql()->select();
        $select->join('eav_attribute', 'eav_attribute.type_id=eav_entity_type.id', ['attr' => 'code', 'type', 'is_required', 'default_value', 'is_unique'], 'left')
                ->where(['eav_entity_type.code' => $this->entityType])
                ->order('sort_order asc');
        $result = $tableGateway->selectWith($select)->toArray();
        if (count($result) === 0) {
            throw new InvalidArgumentException('Invalid entity type code: ' . $this->entityType);
        }
        $select->join($result[0]['entity_table'], $result[0]['entity_table'] . '.type_id=eav_entity_type.id', '*', 'left')
                ->join($result[0]['value_table_prefix'] . '_int', 'eav_attribute.id=' . $result[0]['value_table_prefix'] . '_int.attribute_id AND ' . $result[0]['value_table_prefix'] . '_int.type_id=eav_entity_type.id', ['value_int' => 'value'], 'left')
                ->join($result[0]['value_table_prefix'] . '_varchar', 'eav_attribute.id=' . $result[0]['value_table_prefix'] . '_varchar.attribute_id AND ' . $result[0]['value_table_prefix'] . '_varchar.type_id=eav_entity_type.id', ['value_varchar' => 'value'], 'left')
                ->join($result[0]['value_table_prefix'] . '_datetime', 'eav_attribute.id=' . $result[0]['value_table_prefix'] . '_datetime.attribute_id AND ' . $result[0]['value_table_prefix'] . '_datetime.type_id=eav_entity_type.id', ['value_datetime' => 'value'], 'left')
                ->join($result[0]['value_table_prefix'] . '_blob', 'eav_attribute.id=' . $result[0]['value_table_prefix'] . '_blob.attribute_id AND ' . $result[0]['value_table_prefix'] . '_blob.type_id=eav_entity_type.id', ['value_blob' => 'value'], 'left')
                ->join($result[0]['value_table_prefix'] . '_text', 'eav_attribute.id=' . $result[0]['value_table_prefix'] . '_text.attribute_id AND ' . $result[0]['value_table_prefix'] . '_text.type_id=eav_entity_type.id', ['value_text' => 'value'], 'left')
                ->join($result[0]['value_table_prefix'] . '_decimal', 'eav_attribute.id=' . $result[0]['value_table_prefix'] . '_decimal.attribute_id AND ' . $result[0]['value_table_prefix'] . '_decimal.type_id=eav_entity_type.id', ['value_decimal' => 'value'], 'left');
        $data = $tableGateway->selectWith($select)->toArray();
        $items = [];
        foreach ($data as $record) {
            if (!isset($record['id']) || !$record['id']) {
                continue;
            }
            if (!isset($items[$record['id']])) {
                $items[$record['id']] = [
                    'id' => $record['id'],
                    'store_id' => $record['store_id'],
                    'increment_id' => $record['increment_id'],
                    'status' => $record['status'],
                    'created_at' => $record['created_at']
                ];
            }
            if ($record['attr']) {
                $items[$record['id']][$record['attr']] = $record['value_int']? : (
                        $record['value_varchar']? : (
                                $record['value_decimal']? : (
                                        $record['value_text']? : (
                                                $record['value_datetime']? :
                                                        $record['value_blob']
                                                ))));
            }
        }
        foreach ($items as $set) {
            $this->insert($set);
        }
    }

    public function select($constraint)
    {
        return $this->collection->find($constraint);
    }

    public function update($values, $constraint)
    {
        $this->collection->updateOne($constraint, $values);
    }

    public function upsert($values, $constraint)
    {
        $this->collection->updateOne($constraint, $values, ['upsert' => true]);
    }

}
