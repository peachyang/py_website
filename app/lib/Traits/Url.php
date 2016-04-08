<?php

namespace Seahinet\Lib\Traits;

trait Url
{

    public function getBaseUrl($path = '')
    {
        return $this->getContainer()->get('config')['global/base_url'] . ltrim($path, '/');
    }

    public function getAdminUrl($path = '')
    {
        if (strpos($path, ':ADMIN') !== false) {
            return $this->getBaseUrl(str_replace(':ADMIN', $this->getContainer()->get('config')['global/admin_path'], $path));
        } else {
            return $this->getBaseUrl($this->getContainer()->get('config')['global/admin_path'] . '/' . ltrim($path, '/'));
        }
    }

}
