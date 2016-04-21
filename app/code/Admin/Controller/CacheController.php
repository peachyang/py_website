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
        if ($code) {
            $count = 0;
            if (!is_array($code)) {
                $code = [$code];
            }
            foreach ($code as $prefix) {
                $list = $cache->fetch('CACHE_LIST_' . $prefix);
                if ($list) {
                    foreach ((array) $list as $key => $value) {
                        $cache->delete($key, $prefix);
                    }
                } else {
                    $cache->delete($prefix);
                }
                $count ++;
            }
            $result['message'][] = ['message' => $this->translate('%d cache(s) have been flushed successfully.', [$count]), 'level' => 'success'];
        } else {
            $cache->flushAll();
            $result['message'][] = ['message' => $this->translate('All caches have been flushed successfully.'), 'level' => 'success'];
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            return $result;
        } else {
            $this->addMessage($result['message'], 'danger', 'admin');
            return $this->redirect(':ADMIN/cache/');
        }
    }

}
