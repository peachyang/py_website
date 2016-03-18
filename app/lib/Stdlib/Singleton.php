<?php

namespace Seahinet\Lib\Stdlib;

interface Singleton
{

    /**
     * @return Singleton
     */
    public static function instance();
}
