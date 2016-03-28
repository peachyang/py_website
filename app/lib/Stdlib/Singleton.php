<?php

namespace Seahinet\Lib\Stdlib;

/**
 * Singleton mode.
 * It should provide a private construct method
 */
interface Singleton
{

    /**
     * @static
     * @return self
     */
    public static function instance();
}
