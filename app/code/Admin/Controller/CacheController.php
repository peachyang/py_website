<?php

namespace Seahinet\Admin\Controller;

use Seahinet\Lib\Controller\AuthActionController;

class CacheController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout('admin_cache');
    }

    public function flushAction()
    {
        $code = $this->getRequest()->getQuery('code');
        $cache = $this->getContainer()->get('cache');
        $result = ['message' => [], 'error' => 0];
        $eventDispatcher = $this->getContainer()->get('eventDispatcher');
        if ($code) {
            $count = 0;
            if (!is_array($code)) {
                $code = [$code];
            }
            foreach ($code as $prefix) {
                $eventDispatcher->trigger($prefix . '.cache.delete.before');
                $list = $cache->fetch('CACHE_LIST_' . $prefix);
                if ($list) {
                    foreach ((array) $list as $key => $value) {
                        $cache->delete($key, $prefix);
                    }
                } else {
                    $cache->delete($prefix);
                }
                $eventDispatcher->trigger($prefix . '.cache.delete.after');
                $count ++;
            }
            $result['message'][] = ['message' => $this->translate('%d cache(s) have been flushed successfully.', [$count]), 'level' => 'success'];
        } else {
            $eventDispatcher->trigger('allcache.delete.before');
            $cache->flushAll();
            $eventDispatcher->trigger('allcache.delete.after');
            $result['message'][] = ['message' => $this->translate('All caches have been flushed successfully.'), 'level' => 'success'];
        }
        return $this->response($result, ':ADMIN/cache/');
    }

}
