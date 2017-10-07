<?php

namespace Seahinet\Lib\Db;

use Seahinet\Lib\Bootstrap;
use Zend\Db\Adapter\Adapter as ZendAdapter;

class Adapter extends ZendAdapter
{

    use \Seahinet\Lib\Traits\Container;

    public function query($sql, $parametersOrQueryMode = self::QUERY_MODE_PREPARE, \Zend\Db\ResultSet\ResultSetInterface $resultPrototype = null)
    {
        if (Bootstrap::isDeveloperMode()) {
            $this->getContainer()->get('eventDispatcher')->trigger('db.query.before', ['sql' => $sql, 'params' => $parametersOrQueryMode]);
        }
        $result = parent::query($sql, $parametersOrQueryMode, $resultPrototype);
        if (Bootstrap::isDeveloperMode()) {
            $this->getContainer()->get('eventDispatcher')->trigger('db.query.after', ['sql' => $sql, 'params' => $parametersOrQueryMode, 'result' => $result]);
        }
        return $result;
    }

}
