<?php

namespace Seahinet\Lib\ViewModel;

use Seahinet\Lib\Session\Csrf;

/**
 * View model for renderer
 */
abstract class AbstractViewModel
{

    use \Seahinet\Lib\Traits\Container;

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
     * @var array Variables and children view model
     */
    protected $variables = [];

    public function __construct($template = null)
    {
        $this->template = $template;
    }

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
        if (is_null($this->getTemplate()) || !file_exists(BP . 'app/tpl/' . $this->getTemplate())) {
            return '';
        }
        if ($this->getContainer()->has('renderer')) {
            $rendered = $this->getContainer()->get('renderer')->render($this->getTemplate(), $this);
        } else {
            $rendered = include BP . 'app/tpl/' . $this->getTemplate();
        }
        return $rendered;
    }

    /**
     * @return string
     */
    function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     * @return AbstractViewModel
     */
    function setTemplate($template)
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
        return $this->getVariable($name);
    }

    public function __set($name, $value)
    {
        $this->setVariable($name, $value);
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
        return $this->variables;
    }

    public function setVariables($variables)
    {
        $this->variables = $variables;
        return $this;
    }

}
