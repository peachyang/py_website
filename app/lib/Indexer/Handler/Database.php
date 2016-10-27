<?php

namespace Seahinet\Lib\Indexer\Handler;

use Exception;
use Seahinet\Lib\Exception\BadIndexerException;
use Seahinet\Lib\Db\Sql\Ddl\Column\{
    Timestamp,
    UnsignedInteger
};
use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Ddl;
use Zend\Db\TableGateway\TableGateway;

/**
 * Database indexer handler
 */
class Database extends AbstractHandler
{

    use \Seahinet\Lib\Traits\Container;

    /**
     * @var string
     */
    protected $entityType = null;

    /**
     * @var array
     */
    protected $tableGateways = [];

    public function __construct($entityType)
    {
        $this->entityType = $entityType;
    }

    /**
     * {@inhertdoc}
     */
    public function buildStructure($columns, $keys = null, $extra = null)
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
            $ddl->addColumn(new UnsignedInteger('id', false, 0))
                    ->addColumn(new UnsignedInteger('store_id', false, 0))
                    ->addConstraint(new Ddl\Constraint\PrimaryKey('id'))
                    ->addConstraint(new Ddl\Constraint\ForeignKey('FK_' . strtoupper($table) . '_ID_' . strtoupper($entityTable) . '_ID', 'id', $entityTable, 'id', 'CASCADE', 'CASCADE'))
                    ->addConstraint(new Ddl\Constraint\ForeignKey('FK_' . strtoupper($table) . '_STORE_ID_CORE_STORE_ID', 'store_id', 'core_store', 'id', 'CASCADE', 'CASCADE'));
            if (!is_null($keys)) {
                $ddl->addColumn(new UnsignedInteger('attribute_set_id', false, 0))
                        ->addColumn(new Ddl\Column\Boolean('status', true, 1))
                        ->addColumn(new Timestamp('created_at', true, 'CURRENT_TIMESTAMP'))
                        ->addConstraint(new Ddl\Constraint\ForeignKey('FK_' . strtoupper($table) . '_ATTR_SET_ID_EAV_ATTR_SET_ID', 'attribute_set_id', 'eav_attribute_set', 'id', 'CASCADE', 'CASCADE'));
                foreach (array_diff($keys, [
                    'id', 'store_id', 'status', 'created_at',
                    'updated_at', 'type_id', 'attribute_set_id', 'attr', 'type',
                    'is_required', 'default_value', 'is_unique', 'code', 'entity_table',
                    'value_table_prefix', 'is_form', 'entity_type'
                ]) as $key) {
                    $ddl->addColumn(new Ddl\Column\Varchar($key, 255, true, ''));
                }
            }
            foreach ($columns as $attr) {
                if ($attr['attr']) {
                    if ($attr['type'] === 'int') {
                        $column = new Ddl\Column\Integer($attr['attr'], true, (int) $attr['default_value']);
                    } else if ($attr['type'] === 'varchar') {
                        $column = new Ddl\Column\Varchar($attr['attr'], 255, true, $attr['default_value']);
                    } else if ($attr['type'] === 'datetime') {
                        $column = new Ddl\Column\Datetime($attr['attr'], true, $attr['default_value'] ? date('Y-m-d H:i:s', strtotime($attr['default_value'])) : null);
                    } else if ($attr['type'] === 'decimal') {
                        $column = new Ddl\Column\Decimal($attr['attr'], 12, 4, true, (float) $attr['default_value']);
                    } else {
                        $column = new Ddl\Column\Text($attr['attr'], 65535, true);
                    }
                    $ddl->addColumn($column);
                    if ($attr['is_unique'] && $attr['type'] !== 'text') {
                        #$ddl->addConstraint(new Ddl\Constraint\UniqueKey($attr['attr'], 'UNQ_' . strtoupper($table) . '_' . strtoupper($attr['attr'])));
                        $ddl->addConstraint(new Ddl\Index\Index($attr['attr'], 'IDX_' . strtoupper($table) . '_' . strtoupper($attr['attr'])));
                    }
                }
            }
            if (is_callable($extra)) {
                $extra($ddl);
            }
            $adapter->query(
                    $ddl->getSqlString($platform), $adapter::QUERY_MODE_EXECUTE
            );
        }
    }

    /**
     * {@inhertdoc}
     */
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
            $this->getContainer()->get('log')->logException($e);
        }
    }

    /**
     * Get table gateway based on language id
     * 
     * @param int $languageId
     * @return TableGateway
     */
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

    /**
     * {@inhertdoc}
     */
    public function select($languageId, $where = [], array $options = [])
    {
        try {
            if ($where instanceof Select) {
                return $this->getTableGateway($languageId)->selectWith($where)->toArray();
            }
            return $this->getTableGateway($languageId)->select($where)->toArray();
        } catch (Exception $e) {
            throw new BadIndexerException($e->getMessage());
        }
    }

    /**
     * {@inhertdoc}
     */
    public function insert($languageId, $set, array $options = [])
    {
        try {
            return $this->getTableGateway($languageId)->insert($set);
        } catch (Exception $e) {
            throw new BadIndexerException($e->getMessage());
        }
    }

    /**
     * {@inhertdoc}
     */
    public function update($languageId, $set, $where = [], array $options = [])
    {
        try {
            return $this->getTableGateway($languageId)->update($set, $where);
        } catch (Exception $e) {
            throw new BadIndexerException($e->getMessage());
        }
    }

    /**
     * {@inhertdoc}
     */
    public function upsert($languageId, $set, $where = [], array $options = [])
    {
        $select = $this->select($languageId, $where)->toArray();
        if (count($select)) {
            return $this->update($languageId, $set, $where);
        } else {
            return $this->insert($languageId, $set + $where);
        }
    }

    /**
     * {@inhertdoc}
     */
    public function delete($languageId, $where = [], array $options = [])
    {
        try {
            return $this->getTableGateway($languageId)->delete($where);
        } catch (Exception $e) {
            throw new BadIndexerException($e->getMessage());
        }
    }

}
