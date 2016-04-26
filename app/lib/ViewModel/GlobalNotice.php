<?php

namespace Seahinet\Lib\ViewModel;

use Seahinet\Lib\Stdlib\Singleton;

/**
 * Global notice view model
 */
final class GlobalNotice extends AbstractViewModel implements Singleton
{

    protected static $instance = null;

    private function __construct()
    {
        $this->setTemplate('page/globalNotice');
        $this->cacheKey = 'GLOBAL_NOTICE';
    }

    public static function instance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    public function getNotice()
    {
        return $this->getVariable('notice');
    }

}