<?php

namespace Seahinet\Lib\ViewModel;

use JsonSerializable;
use Seahinet\Lib\Session\Csrf;

/**
 * View model for renderer
 */
abstract class AbstractViewModel
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Lib\Traits\Translate;

    /**
     * @var string|false to disable the cache for this view model
     */
    protected $cacheKey = false;

    /**
     * @var Csrf 
     */
    protected $csrf = null;

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

    public function __toString()
    {
        try {
            return $this->render();
        } catch (\Exception $e) {
            $this->getContainer()->get('log')->logException($e);
            return '';
        }
    }

    /**
     * Render specified template file with specified renderer
     * Use include by default
     * 
     * @return string|mixed
     */
    public function render()
    {
        if (is_null($this->getTemplate())) {
            return $this instanceof JsonSerializable ? $this->jsonSerialize() : '';
        }
        if ($this->getContainer()->has('renderer')) {
            $rendered = $this->getContainer()->get('renderer')->render($this->getTemplate(), $this);
        } else if (file_exists(BP . 'app/tpl/' . $this->getTemplate() . '.phtml')) {
            $rendered = $this->getRendered();
        } else {
            $rendered = '';
        }
        return $rendered;
    }

    /**
     * Render template by default
     * 
     * @return string
     */
    protected function getRendered()
    {
        ob_start();
        include BP . 'app/tpl/' . $this->getTemplate() . '.phtml';
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
    public function getFormKey()
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

}
