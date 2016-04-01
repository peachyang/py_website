<?php

namespace Seahinet\Lib\Session\SaveHandler;

use Seahinet\Lib\Cache as CacheHandler;
use SessionHandlerInterface;

/**
 * Handle session storage with cache
 */
class Cache implements SessionHandlerInterface
{

    /**
     * @var CacheHandler
     */
    protected $cache = null;

    protected function getCache()
    {
        if (is_null($this->cache)) {
            $this->cache = CacheHandler::instance();
        }
        return $this->cache;
    }

    public function close()
    {
        return true;
    }

    public function destroy($session_id)
    {
        return $this->getCache()->delete('SESS_' . $session_id);
    }

    public function gc($maxlifetime)
    {
        return true;
    }

    public function open($save_path, $name)
    {
        return is_null($this->getCache());
    }

    public function read($session_id)
    {
        return $this->getCache()->fetch('SESS_' . $session_id);
    }

    public function write($session_id, $session_data)
    {
        $this->getCache()->save('SESS_' . $session_id, $session_data);
    }

}
