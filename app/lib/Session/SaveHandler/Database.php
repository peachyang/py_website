<?php

namespace Seahinet\Lib\Session\SaveHandler;

use SessionHandlerInterface;
use Zend\Db\TableGateway\TableGateway;

/**
 * Handle session storage with database
 */
class Database implements SessionHandlerInterface
{

    use \Seahinet\Lib\Traits\Container;

    /**
     * @var TableGateway
     */
    private static $tableGateway = null;

    private function getTableGateway()
    {
        if (is_null(static::$tableGateway)) {
            static::$tableGateway = new TableGateway('core_session', $this->getContainer()->get('dbAdapter'));
        }
        return static::$tableGateway;
    }

    public function close()
    {
        return true;
    }

    public function destroy($session_id)
    {
        $this->getTableGateway()->delete(['id' => $session_id]);
    }

    public function gc($maxlifetime)
    {
        return true;
    }

    public function open($save_path, $name)
    {
        return is_null($this->getTableGateway());
    }

    public function read($session_id)
    {
        $result = $this->getTableGateway()->select(['id' => $session_id]);
        return count($result) ? $result[0]['data'] : null;
    }

    public function write($session_id, $session_data)
    {
        if (!$this->getTableGateway()->update(['data' => $session_data], ['id' => $session_id])) {
            $this->getTableGateway()->insert(['data' => $session_data, 'id' => $session_id]);
        }
    }

}
