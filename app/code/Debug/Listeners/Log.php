<?php

namespace Seahinet\Debug\Listeners;

use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Lib\Session\Segment;

class Log implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container;

    protected $segment = null;

    protected function getSegment()
    {
        if (is_null($this->segment)) {
            $this->segment = new Segment('debug');
        }
        return $this->segment;
    }

    public function logSql($e)
    {
        $segment = $this->getSegment();
        $sqls = $segment->get('sql', []);
        $sql = [];
        $sql['sql'] = is_string($e['sql']) ? $e['sql'] : $e['sql']->getSqlString($this->getContainer()->get('dbAdapter')->getPlatform());
        if (substr($sql['sql'], 0, 7) === 'EXPLAIN') {
            return;
        }
        $sql['params'] = $e['params'];
        $sql['count'] = count($e['result']);
        $sqls[] = $sql;
        $segment->set('sql', $sqls);
    }

    public function logCache($e)
    {
        $segment = $this->getSegment();
        $caches = $segment->get('cache', []);
        $prefix = trim($e['prefix'], ' _');
        if (!isset($caches[$prefix])) {
            $caches[$prefix] = [];
        }
        $caches[$prefix][$e['key']] = $e['result'];
        $segment->set('cache', $caches);
    }

    public function logCaches($e)
    {
        foreach ($e['keys'] as $key) {
            $this->logCache(['key' => $key, 'prefix' => $e['prefix'], 'result' => $e['result'][$key]]);
        }
    }

}
