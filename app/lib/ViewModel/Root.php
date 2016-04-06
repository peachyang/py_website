<?php

namespace Seahinet\Lib\ViewModel;

use Seahinet\Lib\Stdlib\Singleton;

/**
 * Root view model
 */
final class Root extends AbstractViewModel implements Singleton
{

    private static $viewModel = null;
    private $bodyClass = [];

    private function __construct()
    {
        $this->setTemplate('page/root');
    }

    public static function instance()
    {
        if (is_null(static::$viewModel)) {
            static::$viewModel = new static;
        }
        return static::$viewModel;
    }

    public function getBodyClass()
    {
        return implode(' ', $this->bodyClass);
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

}
