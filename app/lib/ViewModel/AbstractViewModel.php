<?php

namespace Seahinet\Lib\ViewModel;

use Seahinet\Lib\Session\Csrf;

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

    public function __construct($template = null)
    {
        $this->template = $template;
    }

    /**
     * Render specified template file
     * 
     * @return string|mixed
     */
    public function render()
    {
        if (is_null($this->getTemplate())) {
            return '';
        }
        $rendered = $this->getContainer()->get('renderer')->render(BP . 'app/tpl/' . $this->getTemplate());
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

}
