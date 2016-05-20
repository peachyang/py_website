<?php

namespace Seahinet\Lib\Traits;

use Zend\Db\Adapter\Driver\ConnectionInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Where;
use Zend\Db\TableGateway\TableGateway;

/**
 * Database handler
 */
trait DB
{

    /**
     * @var TableGateway
     */
    protected $tableGateway = null;

    /**
     * @var ConnectionInterface
     */
    protected $connection = null;

    /**
     * @var bool
     */
    protected $transaction = false;

    /**
     * @param string|TableIdentifier|array $table
     * @return TableGateway
     */
    protected function getTableGateway($table = '')
    {
        if (is_null($this->tableGateway)) {
            $this->tableGateway = new TableGateway($table, $this->getContainer()->get('dbAdapter'));
        }
        return $this->tableGateway;
    }

    /**
     * @return \Zend\Db\Adapter\Driver\ConnectionInterface
     */
    protected function getConnection()
    {
        if (is_null($this->connection)) {
            $this->connection = $this->getContainer()->get('dbAdapter')->getDriver()->getConnection();
        }
        return $this->connection;
    }

    /**
     * @param Where|\Closure|string|array $where
     * @param TableGateway $tableGateway
     * @return ResultSet
     */
    protected function select($where = null, $tableGateway = null)
    {
        $tableGateway = is_null($tableGateway) ? $this->tableGateway : $tableGateway;
        if (!is_null($tableGateway)) {
            return $tableGateway->select($where);
        }
        return [];
    }

    /**
     * @param  array $set
     * @param TableGateway $tableGateway
     * @return int
     */
    public function insert($set, $tableGateway = null)
    {
        $tableGateway = is_null($tableGateway) ? $this->tableGateway : $tableGateway;
        if (!is_null($tableGateway)) {
            return $tableGateway->insert($set);
        }
        return 0;
    }

    /**
     * @param  array $set
     * @param  Where|string|array|\Closure $where
     * @param TableGateway $tableGateway
     * @return int
     */
    public function update($set, $where = null, $tableGateway = null)
    {
        $tableGateway = is_null($tableGateway) ? $this->tableGateway : $tableGateway;
        if (!is_null($tableGateway)) {
            return $tableGateway->update($set, $where);
        }
        return 0;
    }

    /**
     * @param array $set
     * @param Where|string|array|\Closure $where
     * @param TableGateway $tableGateway
     * @return int
     */
    public function upsert($set, $where, $tableGateway = null)
    {
        $tableGateway = is_null($tableGateway) ? $this->tableGateway : $tableGateway;
        if (!is_null($tableGateway)) {
            $select = $this->select($where, $tableGateway)->toArray();
            if (count($select)) {
                return $this->update($set, $where, $tableGateway);
            } else {
                return $this->insert($set + $where, $tableGateway);
            }
        }
        return 0;
    }

    /**
     * @param  Where|\Closure|string|array $where
     * @param TableGateway $tableGateway
     * @return int
     */
    public function delete($where, $tableGateway = null)
    {
        $tableGateway = is_null($tableGateway) ? $this->tableGateway : $tableGateway;
        if (!is_null($tableGateway)) {
            return $tableGateway->delete($where);
        }
        return 0;
    }

    /**
     * Begin transaction
     */
    protected function beginTransaction()
    {
        $this->getConnection()->beginTransaction();
        $this->transaction = true;
        return $this;
    }

    /**
     * Commit transaction
     */
    protected function commit()
    {
        $this->getConnection()->commit();
        $this->transaction = false;
        return $this;
    }

    /**
     * Rollback transaction
     */
    protected function rollback()
    {
        $this->getConnection()->rollback();
        $this->transaction = false;
        return $this;
    }

}
