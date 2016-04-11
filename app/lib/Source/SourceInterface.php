<?php

namespace Seahinet\Lib\Source;

/**
 * Array source
 */
interface SourceInterface
{
    /**
     * @return array
     */
    abstract public function getSourceArray();
}
