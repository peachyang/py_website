<?php

namespace Seahinet\Lib\Traits;

use Zend\Db\Adapter\Driver\ConnectionInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Where;
use Zend\Db\TableGateway\TableGateway;

trait DB
{

    use Container;

    /**
     * @var TableGateway
     */
    protected $tableGateway = null;

    /**
     * @var ConnectionInterface
     */
    protected $connection = null;

    /**
     * @param string|TableIdentifier|array $table
     * @return TableGateway
     */
    protected function getTableGateway($table)
    {
        if (is_null($this->tableGateway)) {
            $this->tableGateway = new TableGateway($table, $this->getContainer()->get('dbAdapter'));
        }
        return $this->tableGateway;
    }

    protected function getConnection()
    {
        if (is_null($this->connection) && !is_null($this->tableGateway)) {
            $this->connection = $this->tableGateway->getAdapter()->getDriver()->getConnection();
        }
        return $this->connection;
    }

    /**
     * @param Where|\Closure|string|array $where
     * @return ResultSet
     */
    protected function select($where = null)
    {
        if (!is_null($this->tableGateway)) {
            return $this->tableGateway->select($where);
        }
        return [];
    }

    /**
     * @param  array $set
     * @return int
     */
    public function insert($set)
    {
        if (!is_null($this->tableGateway)) {
            return $this->tableGateway->insert($set);
        }
        return 0;
    }

    /**
     * @param  array $set
     * @param  string|array|\Closure $where
     * @return int
     */
    public function update($set, $where = null)
    {
        if (!is_null($this->tableGateway)) {
            return $this->tableGateway->update($set, $where);
        }
        return 0;
    }

    /**
     * @param  Where|\Closure|string|array $where
     * @return int
     */
    public function delete($where)
    {
        if (!is_null($this->tableGateway)) {
            return $this->tableGateway->delete($where);
        }
        return 0;
    }

    protected function beginTransaction()
    {
        $this->getConnection()->beginTransaction();
        return $this;
    }
    
    protected function commit(){
        $this->getConnection()->commit();
        return $this;
    }

    protected function rollback(){
        $this->getConnection()->rollback();
        return $this;
    }
    
}
