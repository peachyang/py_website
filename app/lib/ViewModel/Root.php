<?php

namespace Seahinet\Lib\ViewModel;

use Seahinet\Lib\Stdlib\Singleton;

/**
 * Root view model
 */
final class Root extends AbstractViewModel implements Singleton
{

    protected static $instance = null;
    protected $bodyClass = [];
    protected $handler = '';

    private function __construct()
    {
        $this->setTemplate('page/root');
    }

    /**
     * @return Root
     */
    public static function instance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    public function getBodyClass($asArray = false)
    {
        return $asArray ? $this->bodyClass : implode(' ', $this->bodyClass);
    }

    public function setBodyClass(array $bodyClass)
    {
        $this->bodyClass = $bodyClass;
        return $this;
    }

    public function addBodyClass($bodyClass)
    {
        $this->bodyClass[] = $bodyClass;
        return $this;
    }

    public function getLang()
    {
        return $this->getContainer()->get('language')['code'];
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function setHandler($handler)
    {
        if ($this->handler === '') {
            $this->addBodyClass(trim(preg_replace('/[^a-z]/', '-', strtolower($handler)), '-'));
        }
        $this->handler = $handler;
        return $this;
    }

}
