<?php

namespace Seahinet\Lib\Indexer\Handler;

use Exception;
use Seahinet\Lib\Exception\BadIndexerException;
use Seahinet\Lib\Db\Sql\Ddl\Column\{
    Timestamp,
    UnsignedInteger
};
use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Sql\{
    Ddl,
    Select
};
use Seahinet\Lib\Db\TableGateway;

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
                    ->addConstraint(new Ddl\Constraint\PrimaryKey('id'));
            if (!is_null($keys)) {
                $ddl->addColumn(new UnsignedInteger('attribute_set_id', false, 0))
                        ->addColumn(new Ddl\Column\Boolean('status', true, 1))
                        ->addColumn(new Timestamp('created_at', true, 'CURRENT_TIMESTAMP'));
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
                        $column = new Ddl\Column\Integer($attr['attr'], true, is_null($attr['default_value']) ? null : (int) $attr['default_value']);
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
        $platform = $adapter->getPlatform();
        try {
            foreach ($data as $languageId => $values) {
                $table = $this->entityType . '_' . $languageId . '_index';
                $columns = [];
                foreach ($values as $sets) {
                    if (count($columns) < count($sets)) {
                        $columns = array_keys($sets);
                    }
                }
                $sql = 'INSERT INTO ' . $platform->quoteIdentifierInFragment($table) . '(';
                foreach ($columns as $key) {
                    $sql .= $platform->quoteIdentifierInFragment($key) . ',';
                }
                $sql = rtrim($sql, ',') . ') VALUES ';
                foreach ($values as $sets) {
                    $set = '';
                    foreach ($columns as $key) {
                        $set .= (isset($sets[$key]) ? $platform->quoteValue($sets[$key]) : 'null') . ',';
                    }
                    $sql .= '(' . rtrim($set, ',') . '),';
                }
                $adapter->query(rtrim($sql, ','), $adapter::QUERY_MODE_EXECUTE);
            }
        } catch (Exception $e) {
            $this->getContainer()->get('log')->logException($e);
        }
    }

    /**
     * {@inhertdoc}
     */
    public function createIndexes($columns, $keys = null)
    {
        $adapter = $this->getContainer()->get('dbAdapter');
        $languages = new Language;
        $entityTable = $columns[0]['entity_table'];
        foreach ($languages as $language) {
            $table = $this->entityType . '_' . $language['id'] . '_index';
            foreach ($columns as $attr) {
                if ($attr['attr'] && $attr['is_unique'] && $attr['type'] !== 'text') {
                    $adapter->query('CREATE INDEX IDX_' . strtoupper($table) . '_' . strtoupper($attr['attr']) . ' ON ' . $table . '(' . $attr['attr'] . ');', $adapter::QUERY_MODE_EXECUTE);
                }
            }
            $adapter->query('CREATE INDEX IDX_' . strtoupper($table) . '_STORE_ID ON ' . $table . '(store_id);', $adapter::QUERY_MODE_EXECUTE);
            $adapter->query('ALTER TABLE ' . $table . ' ADD CONSTRAINT FK_' . strtoupper($table) .
                    '_ID_' . strtoupper($entityTable) . '_ID FOREIGN KEY (id) REFERENCES ' . $entityTable . '(id) ON DELETE CASCADE ON UPDATE CASCADE;', $adapter::QUERY_MODE_EXECUTE);
            $adapter->query('ALTER TABLE ' . $table . ' ADD CONSTRAINT FK_' . strtoupper($table) .
                    '_STORE_ID_CORE_STORE_ID FOREIGN KEY (store_id) REFERENCES core_store(id) ON DELETE CASCADE ON UPDATE CASCADE;', $adapter::QUERY_MODE_EXECUTE);
            if ($keys) {
                $adapter->query('CREATE INDEX IDX_' . strtoupper($table) . '_ATTR_SET_ID' . ' ON ' . $table . '(attribute_set_id);', $adapter::QUERY_MODE_EXECUTE);
                $adapter->query('ALTER TABLE ' . $table . ' ADD CONSTRAINT FK_' . strtoupper($table) . '_ATTR_SET_ID_EAV_ATTR_SET_ID FOREIGN KEY (attribute_set_id) REFERENCES eav_attribute_set(id) ON DELETE CASCADE ON UPDATE CASCADE;', $adapter::QUERY_MODE_EXECUTE);
            }
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
            if (!$where instanceof Select) {
                $where = $this->getTableGateway($languageId)->getSql()->select()->where($where);
                if (!empty($options['limit'])) {
                    $where->limit($options['limit']);
                }
                if (!empty($options['offset'])) {
                    $where->offset($options['offset']);
                }
            }
            return $this->getTableGateway($languageId)->selectWith($where)->toArray();
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
