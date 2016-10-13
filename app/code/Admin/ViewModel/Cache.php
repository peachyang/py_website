<?php

namespace Seahinet\Admin\ViewModel;

class Cache extends Grid
{

    protected $deleteUrl = '';
    protected $action = ['getFlushAction'];
    protected $translateDomain = 'cache';

    public function getFlushAction($item)
    {
        return '<a href="' . $this->getFlushUrl() . '?code=' . $item .
                '" title="' . $this->translate('Flush') .
                '"><span class="fa fa-fw fa-refresh" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Flush') . '</span></a>';
    }

    public function getFlushUrl()
    {
        if ($this->deleteUrl === '') {
            $this->deleteUrl = $this->getAdminUrl(':ADMIN/cache/flush/');
        }
        return $this->deleteUrl;
    }

    protected function prepareCollection($collection = null)
    {
        $list = $this->getContainer()->get('cache')->fetch('CACHE_LIST');
        $result = $list ? array_merge(['SYSTEM_CONFIG', 'ROUTE_CACHE'], array_keys($list)) : ['SYSTEM_CONFIG', 'ROUTE_CACHE'];
        sort($result);
        return $result;
    }

}
