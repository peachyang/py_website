<?php

namespace Seahinet\Admin\Listeners;

use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Lib\Session\Segment;

class LogEvent implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container;

    public function log($event)
    {
        $method = $event['method'];
        if (strpos($method, 'saveAction') !== false) {
            $this->doLog('saved', preg_replace('#Seahinet\\\\Admin\\\\Controller\\\\(.+)Controller#', '$1', get_class($event['controller'])));
        } else if (strpos($method, 'deleteAction') !== false) {
            $this->doLog('deleted', preg_replace('#Seahinet\\\\Admin\\\\Controller\\\\(.+)Controller#', '$1', get_class($event['controller'])));
        }
    }

    protected function doLog($action, $item)
    {
        $user = (new Segment('admin'))->user;
        $this->getContainer()->get('log')->log($user['username'] . ' has ' . $action . ' ' . $item, 200);
    }

}
