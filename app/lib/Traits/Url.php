<?php

namespace Seahinet\Lib\Traits;

use Seahinet\Resource\Model\Resource;

/**
 * Get url
 */
trait Url
{

    /**
     * @var string
     */
    protected $baseUrl = '';

    /**
     * @var string
     */
    protected $pubUrl = '';

    /**
     * Get url based on the website root
     * 
     * @param string $path
     * @return string
     */
    public function getBaseUrl($path = '')
    {
        if ($this->baseUrl === '') {
            $this->baseUrl = $this->getContainer()->get('config')['global/url/base_url'];
        }
        return $this->baseUrl . ltrim($path, '/');
    }

    /**
     * Get url based on the admin page
     * 
     * @param string $path
     * @return string
     */
    public function getAdminUrl($path = '')
    {
        if (strpos($path, ':ADMIN') !== false) {
            return $this->getBaseUrl(str_replace(':ADMIN', $this->getContainer()->get('config')['global/url/admin_path'], $path));
        } else {
            return $this->getBaseUrl($this->getContainer()->get('config')['global/url/admin_path'] . '/' . ltrim($path, '/'));
        }
    }

    /**
     * Get static files url
     * 
     * @param string $path
     * @return string
     */
    public function getPubUrl($path = '')
    {
        if ($this->pubUrl === '') {
            $config = $this->getContainer()->get('config');
            $base = $config['global/url/cookie_free_domain'];
            $mobile = (is_callable([$this, 'isMobile']) ? $this->isMobile() :
                    preg_match('/iPhone|iPod|BlackBerry|Palm|Googlebot-Mobile|Mobile|mobile|mobi|Windows Mobile|Safari Mobile|Android|Opera Mini/', $_SERVER['HTTP_USER_AGENT'])) ?
                    'mobile_' : '';
            $prefix = 'pub/theme/' . $config[is_callable([$this, 'isAdminPage']) && $this->isAdminPage() ?
                    'theme/backend/' . $mobile . 'static' : 'theme/frontend/' . $mobile . 'static'] . '/';
            $this->pubUrl = $base ? ($base . $prefix) : $this->getBaseUrl($prefix);
        }
        return $this->pubUrl . ltrim($path, '/');
    }

    /**
     * Get resource url
     * 
     * @param string $path
     * @return string
     */
    public function getResourceUrl($path = '')
    {
        return $this->getBaseUrl(is_scalar($path) ? Resource::$options['path'] . $path :
                        (is_callable([$path, '__toString']) ? $path->__toString() : Resource::$options['path'] .
                        ($path instanceof Resource ? (substr($path['file_type'], 0, strpos($path['file_type'], '/')) . '/' . $path['real_name']) : ''))
        );
    }

}
