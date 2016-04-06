<?php

namespace Seahinet\Lib\ViewModel;

use Seahinet\Lib\Stdlib\Singleton;

/**
 * Global notice view model
 */
final class GlobalNotice extends AbstractViewModel implements Singleton
{

    private static $viewModel = null;

    private function __construct()
    {
        $this->setTemplate('page/globalNotice');
        $this->cacheKey = 'VIEW_MODEL_GLOBAL_NOTICE';
    }

    public static function instance()
    {
        if (is_null(static::$viewModel)) {
            static::$viewModel = new static;
        }
        return static::$viewModel;
    }

    public function getNotice()
    {
        return $this->getVariable('notice');
    }

}
