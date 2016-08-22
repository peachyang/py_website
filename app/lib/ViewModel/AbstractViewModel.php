<?php

namespace Seahinet\Lib\ViewModel;

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
    abstract public function render();

    /**
     * Get template file path
     * 
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set template file path
     * 
     * @param string $template
     * @param bool $force
     * @return AbstractViewModel
     */
    public function setTemplate($template, $force = true)
    {
        if ($force || empty($this->template)) {
            $this->template = $template;
        }
        return $this;
    }

    /**
     * Get name to cache code
     * 
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }

    /**
     * Get CSRF key value
     * 
     * @return string
     */
    public function getCsrfKey()
    {
        if (is_null($this->csrf)) {
            $this->csrf = new Csrf;
        }
        return $this->csrf->getValue();
    }

    /**
     * Get variable or child view model
     * 
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        if (is_callable([$this, $method])) {
            return $this->$method();
        }
        return $this->getVariable($name)? : $this->getChild($name);
    }

    /**
     * Returns the variable at the specified key
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getVariable($key, $default = '')
    {
        return isset($this->variables[$key]) ? $this->variables[$key] : $default;
    }

    /**
     * Sets the value at the specified key to value
     * 
     * @param string $key
     * @param mixed $value
     * @return AbstractViewModel
     */
    public function setVariable($key, $value)
    {
        $this->variables[$key] = $value;
        return $this;
    }

    /**
     * Get variables and children view models
     * 
     * @return array
     */
    public function getVariables()
    {
        return $this->variables + $this->children;
    }

    /**
     * Sets variables
     * 
     * @param array $variables
     * @return AbstractViewModel
     */
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

    /**
     * Serialize this view model
     * 
     * @return string
     */
    public function serialize()
    {
        return serialize(array_filter(get_object_vars($this), function($value) {
                    return !is_object($value);
                })
        );
    }

    /**
     * Unserialize this view model
     * 
     * @param string $serialized
     */
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

    /**
     * Get request
     * 
     * @return \Seahinet\Lib\Http\Request
     */
    protected function getRequest()
    {
        if (is_null($this->request)) {
            $this->request = $this->getContainer()->get('request');
        }
        return $this->request;
    }

    /**
     * Get request query
     * 
     * @return array
     */
    public function getQuery($key = null, $default = '')
    {
        if (is_null($this->query)) {
            $this->query = $this->getRequest()->getQuery();
        }
        return is_null($key) ? $this->query : (isset($this->query[$key]) ? $this->query[$key] : $default);
    }

    /**
     * Get request uri
     * 
     * @return \Seahinet\Lib\Http\Uri
     */
    public function getUri()
    {
        if (is_null($this->uri)) {
            $this->uri = $this->getRequest()->getUri();
        }
        return $this->uri;
    }

    /**
     * Is admin page
     * 
     * @return bool
     */
    public function isAdminPage()
    {
        if (is_null(self::$isAdmin)) {
            self::$isAdmin = in_array('admin', Root::instance()->getBodyClass(true));
        }
        return self::$isAdmin;
    }

    /**
     * Get system config
     * 
     * @return \Seahinet\Lib\Config
     */
    public function getConfig()
    {
        if (is_null($this->config)) {
            $this->config = $this->getContainer()->get('config');
        }
        return $this->config;
    }

}
