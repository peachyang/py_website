<?php

namespace Seahinet\Admin\Listeners;

use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Lib\Session\Segment;

class Visitors implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container;

    public function stat()
    {
        $segment = new Segment('core');
        if (!$segment->get('statUv')) {
            $segment->set('statUv', 1);
            $cache = $this->getContainer()->get('cache');
            $count = $cache->fetch('UV', 'STAT_');
            if ($count) {
                $count[time()] ++;
            } else {
                $count = [time() => 1];
            }
            $cache->save('UV', $count, 'STAT_');
        }
    }

}
