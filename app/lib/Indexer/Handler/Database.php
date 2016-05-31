<?php

namespace Seahinet\Lib\Indexer\Handler;

use Exception;
use Seahinet\Lib\Exception\BadIndexerException;
use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Sql\Ddl;
use Zend\Db\TableGateway\TableGateway;

class Database extends AbstractHandler
{

    use \Seahinet\Lib\Traits\Container;

    protected $entityType = null;
    protected $tableGateways = [];

    public function __construct($entityType)
    {
        $this->entityType = $entityType;
    }

    protected function buildStructure($columns)
    {
        $adapter = $this->getContainer()->get('dbAdapter');
        $platform = $adapter->getPlatform();
        $languages = new Language;
        $entityTable = $columns[0]['entity_table'];
        foreach ($languages as $language) {
            $table = $this->entityType . '_' . $language['id'] . '_index';
            $adapter->query(
                    'DROP TABLE IF EXISTS ' . $table, $adapter::QUERY_MODE_EXECUTE
            );
            $ddl = new Ddl\CreateTable($table);
            $ddl->addColumn(new Ddl\Column\Integer('id', false, 0))
                    ->addColumn(new Ddl\Column\Integer('store_id', false, 0, ['unsigned' => 1]))
                    ->addColumn(new Ddl\Column\Varchar('increment_id', 255, true, ''))
                    ->addColumn(new Ddl\Column\Boolean('status', true, 1))
                    ->addColumn(new Ddl\Column\Timestamp('created_at', true))
                    ->addConstraint(new Ddl\Constraint\PrimaryKey('id'))
                    ->addConstraint(new Ddl\Constraint\ForeignKey('FK_' . strtoupper($table) . '_ID_' . strtoupper($entityTable) . '_ID', 'id', $entityTable, 'id', 'CASCADE', 'CASCADE'))
                    ->addConstraint(new Ddl\Constraint\ForeignKey('FK_' . strtoupper($table) . '_STORE_ID_CORE_STORE_ID', 'store_id', 'core_store', 'id', 'CASCADE', 'CASCADE'));
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
                        $ddl->addConstraint(new Ddl\Constraint\UniqueKey($attr['attr'], 'UNQ_' . strtoupper($table) . '_' . strtoupper($attr['attr'])));
                    }
                }
            }
            $adapter->query(
                    $ddl->getSqlString($platform), $adapter::QUERY_MODE_EXECUTE
            );
        }
    }

    public function buildData($data)
    {
        $adapter = $this->getContainer()->get('dbAdapter');
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        $tableGateways = [];
        try {
            foreach ($data as $languageId => $values) {
                if (!isset($tableGateways[$languageId])) {
                    $tableGateways[$languageId] = new TableGateway($this->entityType . '_' . $languageId . '_index', $adapter);
                }
                foreach ($values as $set) {
                    $tableGateways[$languageId]->insert($set);
                }
            }
            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
        }
    }

    protected function getTableGateway($languageId)
    {
        if (is_array($languageId) || is_object($languageId)) {
            $languageId = $languageId['id'];
        }
        if (!isset($this->tableGateways[$languageId])) {
            $this->tableGateways[$languageId] = new TableGateway($this->entityType . '_' . $languageId . '_index', $this->getContainer()->get('dbAdapter'));
        }
        return $this->tableGateways[$languageId];
    }

    public function select($languageId, $where = null)
    {
        try {
            return $this->getTableGateway($languageId)->select($where)->toArray();
        } catch (Exception $e) {
            throw new BadIndexerException($e->getMessage());
        }
    }

    public function insert($languageId, $set)
    {
        try {
            return $this->getTableGateway($languageId)->insert($set);
        } catch (Exception $e) {
            throw new BadIndexerException($e->getMessage());
        }
    }

    public function update($languageId, $set, $where = null)
    {
        try {
            return $this->getTableGateway($languageId)->update($set, $where);
        } catch (Exception $e) {
            throw new BadIndexerException($e->getMessage());
        }
    }

    public function upsert($languageId, $set, $where)
    {
        $select = $this->select($languageId, $where)->toArray();
        if (count($select)) {
            return $this->update($languageId, $set, $where);
        } else {
            return $this->insert($languageId, $set + $where);
        }
    }

    public function delete($languageId, $where)
    {
        try {
            return $this->getTableGateway($languageId)->delete($where);
        } catch (Exception $e) {
            throw new BadIndexerException($e->getMessage());
        }
    }

}
