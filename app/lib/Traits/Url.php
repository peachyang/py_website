<?php

namespace Seahinet\Lib\Traits;

trait Url
{

    public function getBaseUrl($path = '')
    {
        return $this->getContainer()->get('config')['global/url/base_url'] . ltrim($path, '/');
    }

    public function getAdminUrl($path = '')
    {
        if (strpos($path, ':ADMIN') !== false) {
            return $this->getBaseUrl(str_replace(':ADMIN', $this->getContainer()->get('config')['global/url/admin_path'], $path));
        } else {
            return $this->getBaseUrl($this->getContainer()->get('config')['global/url/admin_path'] . '/' . ltrim($path, '/'));
        }
    }

}
