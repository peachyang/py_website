<?php

namespace Seahinet\Lib\Db;

use Seahinet\Lib\Bootstrap;
use Zend\Db\Sql\{
    Delete,
    Insert,
    Select,
    Update
};
use Zend\Db\TableGateway\TableGateway as ZendTableGateway;

class TableGateway extends ZendTableGateway
{

    use \Seahinet\Lib\Traits\Container;
    
    protected function executeDelete(Delete $delete)
    {
        if (Bootstrap::isDeveloperMode()) {
            $this->getContainer()->get('eventDispatcher')->trigger('db.query.before', ['sql' => $delete, 'params' => []]);
        }
        $result = parent::executeDelete($delete);
        if (Bootstrap::isDeveloperMode()) {
            $this->getContainer()->get('eventDispatcher')->trigger('db.query.after', ['sql' => $delete, 'params' => [], 'result' => $result]);
        }
        return $result;
    }

    protected function executeInsert(Insert $insert)
    {
        if (Bootstrap::isDeveloperMode()) {
            $this->getContainer()->get('eventDispatcher')->trigger('db.query.before', ['sql' => $insert, 'params' => []]);
        }
        $result = parent::executeInsert($insert);
        if (Bootstrap::isDeveloperMode()) {
            $this->getContainer()->get('eventDispatcher')->trigger('db.query.after', ['sql' => $insert, 'params' => [], 'result' => $result]);
        }
        return $result;
    }

    protected function executeSelect(Select $select)
    {
        if (Bootstrap::isDeveloperMode()) {
            $this->getContainer()->get('eventDispatcher')->trigger('db.query.before', ['sql' => $select, 'params' => []]);
        }
        $result = parent::executeSelect($select);
        if (Bootstrap::isDeveloperMode()) {
            $this->getContainer()->get('eventDispatcher')->trigger('db.query.after', ['sql' => $select, 'params' => [], 'result' => $result]);
        }
        return $result;
    }

    protected function executeUpdate(Update $update)
    {
        if (Bootstrap::isDeveloperMode()) {
            $this->getContainer()->get('eventDispatcher')->trigger('db.query.before', ['sql' => $update, 'params' => []]);
        }
        $result = parent::executeUpdate($update);
        if (Bootstrap::isDeveloperMode()) {
            $this->getContainer()->get('eventDispatcher')->trigger('db.query.after', ['sql' => $update, 'params' => [], 'result' => $result]);
        }
        return $result;
    }

}
