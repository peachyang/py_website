<?php

namespace Seahinet\Lib\ViewModel;

use JsonSerializable;
use Seahinet\Lib\Session\Csrf;
use Seahinet\Lib\Stdlib\Singleton;
use Seahinet\Lib\ViewModel\Root;
use Serializable;

/**
 * View model for renderer
 */
abstract class AbstractViewModel implements Serializable
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Lib\Traits\Translate,
        \Seahinet\Lib\Traits\Url;

    /**
     * @var string|false to disable the cache for this view model
     */
    protected $cacheKey = false;

    /**
     * @var Csrf 
     */
    protected $csrf = null;

    /**
     * @var array
     */
    protected $query = null;

    /**
     * @var \Seahinet\Lib\Http\Uri
     */
    protected $uri = null;

    /**
     * @var string
     */
    protected $pubUrl = '';

    /**
     * @var bool 
     */
    private static $isAdmin = null;

    /**
     * @var string
     */
    protected $template = null;

    /**
     * @var array Variables
     */
    protected $variables = [];

    /**
     * @var array Children view model
     */
    protected $children = [];

    /**
     * @var \Seahinet\Lib\Http\Request 
     */
    protected $request = null;

    /**
     * @var \Seahinet\Lib\Config 
     */
    protected $config = null;

    public function __toString()
    {
        return $this->render();
    }

    /**
     * Render specified template file with specified renderer
     * Use include by default
     * 
     * @return string|mixed
     */
    public function render()
    {
        try {
            if (is_null($this->getTemplate())) {
                return $this instanceof JsonSerializable ? $this->jsonSerialize() : '';
            }
            if ($this->getCacheKey()) {
                $lang = $this->getContainer()->get('language')['code'];
                $cache = $this->getContainer()->get('cache');
                $rendered = $cache->fetch($lang . $this->getCacheKey(), 'VIEWMODEL_RENDERED_');
                if ($rendered) {
                    return $rendered;
                }
            }
            $template = BP . 'app/tpl/' . $this->getConfig()[$this->isAdminPage() ? 'theme/backend/template' : 'theme/frontend/template'] . DS . $this->getTemplate();
            if ($this->getContainer()->has('renderer')) {
                $rendered = $this->getContainer()->get('renderer')->render($template, $this);
            } else if (file_exists($template . '.phtml')) {
                $rendered = $this->getRendered($template . '.phtml');
            } else {
                $rendered = '';
            }
            if ($this->getCacheKey()) {
                $cache->save($lang . $this->getCacheKey(), $rendered, 'VIEWMODEL_RENDERED_');
            }
            return $rendered;
        } catch (\Exception $e) {
            $this->getContainer()->get('log')->logException($e);
            return '';
        }
    }

    /**
     * Render template by default
     * 
     * @param string $template
     * @return string
     */
    protected function getRendered($template)
    {
        ob_start();
        include $template;
        return ob_get_clean();
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     * @return AbstractViewModel
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }

    /**
     * @return string
     */
    public function getCsrfKey()
    {
        if (is_null($this->csrf)) {
            $this->csrf = new Csrf;
        }
        return $this->csrf->getValue();
    }

    public function __get($name)
    {
        return $this->getVariable($name)? : $this->getChild($name);
    }

    public function getVariable($key)
    {
        return isset($this->variables[$key]) ? $this->variables[$key] : '';
    }

    public function setVariable($key, $value)
    {
        $this->variables[$key] = $value;
        return $this;
    }

    public function getVariables()
    {
        return $this->children + $this->variables;
    }

    public function setVariables(array $variables)
    {
        foreach ($variables as $key => $value) {
            $this->setVariable($key, $value);
        }
        return $this;
    }

    /**
     * Get child view model
     * 
     * @param string $name
     * @param bool $recursive
     * @return AbstractViewModel
     */
    public function getChild($name = null, $recursive = false)
    {
        if (is_null($name)) {
            return $this->children;
        } else if (isset($this->children[$name])) {
            return $this->children[$name];
        } else if ($recursive) {
            foreach ($this->children as $value) {
                $child = $value->getChild($name, $recursive);
                if (!is_null($child)) {
                    return $child;
                }
            }
        }
        return null;
    }

    /**
     * Add child view model
     * 
     * @param string $name
     * @param AbstractViewModel $child
     * @return AbstractViewModel
     */
    public function addChild($name, AbstractViewModel $child)
    {
        $this->children[$name] = $child;
        return $this;
    }

    public function serialize()
    {
        return serialize(array_filter(get_object_vars($this), function($value) {
                    return !is_object($value);
                })
        );
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
        if ($this instanceof Singleton) {
            static::$instance = $this;
        }
    }

    protected function getRequest()
    {
        if (is_null($this->request)) {
            $this->request = $this->getContainer()->get('request');
        }
        return $this->request;
    }

    public function getQuery($key = null, $default = '')
    {
        if (is_null($this->query)) {
            $this->query = $this->getRequest()->getQuery();
        }
        return is_null($key) ? $this->query : (isset($this->query[$key]) ? $this->query[$key] : $default);
    }

    public function getUri()
    {
        if (is_null($this->uri)) {
            $this->uri = $this->getRequest()->getUri();
        }
        return $this->uri;
    }

    public function isAdminPage()
    {
        if (is_null(self::$isAdmin)) {
            self::$isAdmin = in_array('admin', Root::instance()->getBodyClass(true));
        }
        return self::$isAdmin;
    }

    public function getPubUrl($path = '')
    {
        if ($this->pubUrl === '') {
            $config = $this->getConfig();
            $base = $config['global/url/cookie_free_domain'];
            $prefix = 'pub/theme/' . $config[$this->isAdminPage() ?
                            'theme/backend/static' : 'theme/frontend/static'] . '/';
            $this->pubUrl = $base ? ($base . $prefix) : $this->getBaseUrl($prefix);
        }
        return $this->pubUrl . ltrim($path, '/');
    }

    public function getConfig()
    {
        if (is_null($this->config)) {
            $this->config = $this->getContainer()->get('config');
        }
        return $this->config;
    }

}
