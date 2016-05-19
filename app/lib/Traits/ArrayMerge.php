<?php

namespace Seahinet\Lib\Traits;

trait ArrayMerge
{

    /**
     * Merge array recursively
     * 
     * @param array $a
     * @param array $b
     * @return array
     */
    protected function arrayMerge(array $a, array $b)
    {
        foreach ($b as $key => $value) {
            if (is_numeric($key)) {
                return array_merge_recursive($a, $b);
            }
            if (isset($a[$key])) {
                $a[$key] = is_array($value) ? $this->arrayMerge($a[$key], $value) : $value;
            } else {
                $a[$key] = $value;
            }
        }
        return $a;
    }

}
