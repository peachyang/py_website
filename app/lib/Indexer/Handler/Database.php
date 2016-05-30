<?php

namespace Seahinet\Lib\Indexer\Handler;

use InvalidArgumentException;
use Zend\Db\Sql\Ddl;
use Zend\Db\TableGateway\TableGateway;

class Database implements HandlerInterface
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Lib\Traits\DB;

    protected $entityType = null;

    public function __construct($entityType)
    {
        $this->getTableGateway($entityType . '_index');
        $this->entityType = $entityType;
    }

    public function reindex()
    {
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
        $this->buildStructure($result, $adapter);
        $select->join($result[0]['entity_table'], $result[0]['entity_table'] . '.type_id=eav_entity_type.id', '*', 'left')
                ->join($result[0]['value_table_prefix'] . '_int', 'eav_attribute.id=' . $result[0]['value_table_prefix'] . '_int.attribute_id AND ' . $result[0]['value_table_prefix'] . '_int.type_id=eav_entity_type.id', ['value_int' => 'value'], 'left')
                ->join($result[0]['value_table_prefix'] . '_varchar', 'eav_attribute.id=' . $result[0]['value_table_prefix'] . '_varchar.attribute_id AND ' . $result[0]['value_table_prefix'] . '_varchar.type_id=eav_entity_type.id', ['value_varchar' => 'value'], 'left')
                ->join($result[0]['value_table_prefix'] . '_datetime', 'eav_attribute.id=' . $result[0]['value_table_prefix'] . '_datetime.attribute_id AND ' . $result[0]['value_table_prefix'] . '_datetime.type_id=eav_entity_type.id', ['value_datetime' => 'value'], 'left')
                ->join($result[0]['value_table_prefix'] . '_blob', 'eav_attribute.id=' . $result[0]['value_table_prefix'] . '_blob.attribute_id AND ' . $result[0]['value_table_prefix'] . '_blob.type_id=eav_entity_type.id', ['value_blob' => 'value'], 'left')
                ->join($result[0]['value_table_prefix'] . '_text', 'eav_attribute.id=' . $result[0]['value_table_prefix'] . '_text.attribute_id AND ' . $result[0]['value_table_prefix'] . '_text.type_id=eav_entity_type.id', ['value_text' => 'value'], 'left')
                ->join($result[0]['value_table_prefix'] . '_decimal', 'eav_attribute.id=' . $result[0]['value_table_prefix'] . '_decimal.attribute_id AND ' . $result[0]['value_table_prefix'] . '_decimal.type_id=eav_entity_type.id', ['value_decimal' => 'value'], 'left');
        $this->buildData($tableGateway->selectWith($select)->toArray());
    }

    protected function buildStructure($columns, $adapter)
    {
        $platform = $adapter->getPlatform();
        $adapter->query(
                'DROP TABLE IF EXISTS ' . $this->entityType . '_index;', $adapter::QUERY_MODE_EXECUTE
        );
        $ddl = new Ddl\CreateTable($this->entityType . '_index');
        $ddl->addColumn(new Ddl\Column\Integer('id', false, 0))
                ->addColumn(new Ddl\Column\Integer('store_id', false, 0, ['unsigned' => 1]))
                ->addColumn(new Ddl\Column\Varchar('increment_id', 255, true, ''))
                ->addColumn(new Ddl\Column\Boolean('status', true, 1))
                ->addColumn(new Ddl\Column\Timestamp('created_at', false))
                ->addConstraint(new Ddl\Constraint\PrimaryKey('id'));
        foreach ($columns as $attr) {
            if ($attr['attr']) {
                if ($attr['type'] === 'int') {
                    $column = new Ddl\Column\Integer($attr['attr'], (bool) $attr['is_required'], $attr['default_value'], ['unsigned' => 1]);
                } else if ($attr['type'] === 'varchar') {
                    $column = new Ddl\Column\Varchar($attr['attr'], 255, (bool) $attr['is_required'], $attr['default_value']);
                } else if ($attr['type'] === 'datetime') {
                    $column = new Ddl\Column\Timestamp($attr['attr'], (bool) $attr['is_required'], $attr['default_value']);
                } else if ($attr['type'] === 'decimal') {
                    $column = new Ddl\Column\Decimal($attr['attr'], 12, 4, (bool) $attr['is_required'], $attr['default_value']);
                } else if ($attr['type'] === 'blob') {
                    $column = new Ddl\Column\Blob($attr['attr'], 65535, (bool) $attr['is_required'], $attr['default_value']);
                } else {
                    $column = new Ddl\Column\Text($attr['attr'], 65535, (bool) $attr['is_required'], $attr['default_value']);
                }
                $ddl->addColumn($column);
                if ($attr['is_unique']) {
                    $ddl->addConstraint(new Ddl\Constraint\UniqueKey($attr['attr'], 'UNQ_' . strtoupper($this->entityType) . '_INDEX_' . strtoupper($attr['attr'])));
                }
            }
        }
        $adapter->query(
                $ddl->getSqlString($platform), $adapter::QUERY_MODE_EXECUTE
        );
    }

    public function buildData($data)
    {
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

}
