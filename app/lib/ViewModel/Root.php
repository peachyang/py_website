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

    public function getBodyClass($array = false)
    {
        return $array ? $this->bodyClass : implode(' ', $this->bodyClass);
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

}
