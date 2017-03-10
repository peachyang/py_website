<?php

namespace Seahinet\Admin\ViewModel;

class Cache extends Grid
{

    protected $action = ['getFlushAction' => 'Admin\\Cache::flush'];
    protected $translateDomain = 'cache';

    public function getFlushAction($item)
    {
        return '<a href="' . $this->getAdminUrl(':ADMIN/cache/flush/') . '?code=' . $item .
                '" title="' . $this->translate('Flush') .
                '"><span class="fa fa-fw fa-refresh" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Flush') . '</span></a>';
    }

    public function getFlushUrl()
    {
        return $this->hasPermission('Admin\\Cache::flush') ? $this->getAdminUrl(':ADMIN/cache/flush/') : '';
    }

    protected function prepareCollection($collection = null)
    {
        $cache = $this->getContainer()->get('cache');
        $list = $cache->fetch($cache->salt . 'CACHE_LIST');
        $result = $list ? array_merge(['ROUTE_CACHE'], array_keys($list)) : ['ROUTE_CACHE'];
        sort($result);
        return $result;
    }

}
