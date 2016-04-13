<?php

namespace Seahinet\Lib\Stdlib;

/**
 * Singleton mode.
 * It should provide a private construct method
 */
interface Singleton
{

    /**
     * @return Singleton
     */
    public static function instance();
}
