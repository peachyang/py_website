<?php

namespace Seahinet\Admin\Controller;

use DateTime;
use Seahinet\Lib\Controller\AuthActionController;

class DashboardController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout('admin_dashboard');
    }

    public function visitorsAction()
    {
        $filter = $this->getRequest()->getQuery('filter', 'd');
        $cache = $this->getContainer()->get('cache');
        $visitors = $cache->fetch('UV', 'STAT_');
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
        if ($visitors) {
            $current = new DateTime;
            $keys = [];
            foreach ($visitors as $time => $count) {
                if (is_numeric($time)) {
                    $time = date(DateTime::RFC3339, $time);
                }
                $time = new DateTime($time);
                $diff = $current->diff($time);
                if ($diff->d < 1) {
                    $result['daily'] += $count;
                    if ($filter === 'd') {
                        $result['filted'][$diff->h + 1] += $count;
                    }
                }
                if ($diff->m < 1) {
                    $result['monthly'] += $count;
                    if ($filter === 'm') {
                        $result['filted'][$diff->d + 1] += $count;
                    }
                }
                if ($diff->y < 1) {
                    $result['yearly'] += $count;
                    if ($filter === 'y') {
                        $result['filted'][$diff->m + 1] += $count;
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
                        $result['filted'][$key] += $count;
                        $keys[$key] = 1;
                    }
                    if ($ts >= $from2 && $ts <= $to2) {
                        if (!isset($result['compared'][$key]) || is_null($result['compared'][$key])) {
                            $result['compared'][$key] = 0;
                        }
                        $result['compared'][$key] += $count;
                        $keys[$key] = 1;
                    }
                }
                $result['amount'] += $count;
            }
            if (!empty($keys)) {
                $result['keys'] = array_keys($keys);
            }
        }
        return $result;
    }

}
