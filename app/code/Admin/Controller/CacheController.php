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
                $list = $cache->fetch('CACHE_LIST_' . $prefix);
                $eventDispatcher->trigger($prefix . '.cache.delete.before', ['prefix' => $prefix, 'list' => $list]);
                if ($list) {
                    foreach ((array) $list as $key => $value) {
                        if ($key === 'SYSTEM_CONFIG') {
                            $this->flushShmop();
                        } else {
                            $cache->delete($key, $prefix);
                        }
                    }
                } else {
                    $cache->delete('', $prefix);
                    $this->flushShmop();
                }
                $eventDispatcher->trigger($prefix . '.cache.delete.after', ['prefix' => $prefix]);
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

    public function flushShmop()
    {
        if (extension_loaded('shmop')) {
            $ftok = function_exists('ftok') ? 'ftok' : function($pathname, $proj) {
                $st = @stat($pathname);
                if (!$st) {
                    return -1;
                }
                $key = sprintf("%u", (($st['ino'] & 0xffff) | (($st['dev'] & 0xff) << 16) | (($proj & 0xff) << 24)));
                return $key;
            };
            $shmid = shmop_open($ftok(BP . 'app/lib/Bootstrap.php', 'R'), 'w', 0644, 524288);
            shmop_delete($shmid);
            shmop_close($shmid);
        }
    }

}
