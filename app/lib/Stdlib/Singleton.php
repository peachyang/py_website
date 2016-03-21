<?php

namespace Seahinet\Lib\Stdlib;

interface Singleton
{

    /**
     * @static
     * @return Singleton
     */
    public static function instance();
}
