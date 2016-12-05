<?php

namespace Seahinet\Admin\Traits;

use DateTime;

trait Stat
{

    protected function stat($collection, callable $getTime, $getItem = null)
    {
        $filter = $this->getRequest()->getQuery('filter', 'd');
        if ($filter === 'd') {
            $filted = array_fill(1, 24, 0);
        } else if ($filter === 'm') {
            $filted = array_fill(1, 30, 0);
        } else if ($filter === 'y') {
            $filted = array_fill(1, 12, 0);
        } else {
            $filted = [];
            $from1 = strtotime($this->getRequest()->getQuery('from1', 0));
            $from2 = strtotime($this->getRequest()->getQuery('from2', 0));
            $to1 = strtotime($this->getRequest()->getQuery('to1', 0));
            $to2 = strtotime($this->getRequest()->getQuery('to2', 0));
        }
        $result = [
            'amount' => 0,
            'daily' => 0,
            'monthly' => 0,
            'yearly' => 0,
            'filted' => $filted,
            'keys' => array_keys($filted)
        ];
        if (count($collection)) {
            $current = new DateTime;
            $keys = [];
            foreach ($collection as $key => $value) {
                $time = $getTime($value, $key);
                $diff = $current->diff($time);
                $item = is_callable($getItem) ? $getItem($value, $key, $time) : 1;
                if ($diff->d < 1) {
                    $result['daily'] += $item;
                    if ($filter === 'd') {
                        $result['filted'][$diff->h + 1] += $item;
                    }
                }
                if ($diff->m < 1 && $diff->d < 30) {
                    $result['monthly'] += $item;
                    if ($filter === 'm') {
                        $result['filted'][$diff->d + 1] += $item;
                    }
                }
                if ($diff->y < 1) {
                    $result['yearly'] += $item;
                    if ($filter === 'y') {
                        $result['filted'][$diff->m + 1] += $item;
                    }
                }
                if ($filter === 'c') {
                    $ts = $time->getTimestamp();
                    $key = date('Y-m-d', $ts);
                    $result['compared'] = [];
                    if ($ts >= $from1 && $ts <= $to1) {
                        if (!isset($result['filted'][$key])) {
                            $result['filted'][$key] = 0;
                        }
                        $result['compared'][$key] = null;
                        $result['filted'][$key] += $item;
                        $keys[$key] = 1;
                    }
                    if ($ts >= $from2 && $ts <= $to2) {
                        if (!isset($result['compared'][$key])) {
                            $result['compared'][$key] = 0;
                        }
                        $result['compared'][$key] += $item;
                        $keys[$key] = 1;
                    }
                }
                $result['amount'] += $item;
            }
            if (!empty($keys)) {
                $result['keys'] = array_keys($keys);
            }
        }
        return $result;
    }

}
