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
        $start = $this->translate('Start Date');
        $end = $this->translate('End Date');
        if ($filter === 'd') {
            $filted = array_fill(1, 24, 0);
        } else if ($filter === 'm') {
            $filted = array_fill(1, 30, 0);
        } else if ($filter === 'y') {
            $filted = array_fill(1, 12, 0);
        } else {
            $filted = [$start => 0, $end => 0];
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
            'filted' => $filted
        ];
        if ($visitors) {
            $current = new DateTime();
            foreach ($visitors as $timestamp => $count) {
                $time = new DateTime(date('Y-m-d h:i:s', $timestamp));
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
                    if ($ts >= $from1 && $ts <= $to1) {
                        $result['filted'][$start] += $count;
                    }
                    if ($ts >= $from2 && $ts <= $to2) {
                        $result['filted'][$end] += $count;
                    }
                }
                $result['amount'] += $count;
            }
        }
        return $result;
    }

}
