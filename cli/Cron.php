<?php

namespace Seahinet\Cli;

require __DIR__ . '/../app/bootstrap.php';

use DateTime;
use Error;
use Exception;
use Seahinet\Lib\Model\Collection\Cron as Collection;
use Seahinet\Lib\Model\Cron as Model;

class Cron extends AbstractCli
{

    use \Seahinet\Lib\Traits\Container;

    protected $config = null;

    protected function getConfig()
    {
        if (is_null($this->config)) {
            $this->config = $this->getContainer()->get('config')['cron'];
        }
        return $this->config;
    }

    public function matchCronExpression($expr, $num)
    {
        if ($expr === '*') {
            return true;
        }

        if (strpos($expr, ',') !== false) {
            foreach (explode(',', $expr) as $e) {
                if ($this->matchCronExpression($e, $num)) {
                    return true;
                }
            }
            return false;
        }

        if (strpos($expr, '/') !== false) {
            $e = explode('/', $expr);
            if (sizeof($e) !== 2) {
                throw new Exception('Invalid cron expression, expecting "match/modulus": ' . $expr);
            }
            if (!is_numeric($e[1])) {
                throw new Exception('Invalid cron expression, expecting numeric modulus: ' . $expr);
            }
            $expr = $e[0];
            $mod = $e[1];
        } else {
            $mod = 1;
        }

        if ($expr === '*') {
            $from = 0;
            $to = 60;
        } elseif (strpos($expr, '-') !== false) {
            $e = explode('-', $expr);
            if (sizeof($e) !== 2) {
                throw new Exception('Invalid cron expression, expecting "from-to" structure: ' . $expr);
            }

            $from = $this->getNumeric($e[0]);
            $to = $this->getNumeric($e[1]);
        } else {
            $from = $this->getNumeric($expr);
            $to = $from;
        }

        if ($from === false || $to === false) {
            throw new Exception('Invalid cron expression: ' . $expr);
        }

        return ($num >= $from) && ($num <= $to) && ($num % $mod === 0);
    }

    public function getNumeric($value)
    {
        static $data = array(
            'jan' => 1,
            'feb' => 2,
            'mar' => 3,
            'apr' => 4,
            'may' => 5,
            'jun' => 6,
            'jul' => 7,
            'aug' => 8,
            'sep' => 9,
            'oct' => 10,
            'nov' => 11,
            'dec' => 12,
            'sun' => 0,
            'mon' => 1,
            'tue' => 2,
            'wed' => 3,
            'thu' => 4,
            'fri' => 5,
            'sat' => 6,
        );

        if (is_numeric($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = strtolower(substr($value, 0, 3));
            if (isset($data[$value])) {
                return $data[$value];
            }
        }

        return false;
    }

    protected function addJob()
    {
        if (!empty($this->getConfig())) {
            $count = 0;
            for ($ts = time();; $ts+=60) {
                $d = getdate($ts);
                foreach ($this->getConfig() as $config) {
                    if (!isset($config['time']) || !isset($config['code'])) {
                        continue;
                    }
                    $e = explode(' ', $config['time']);
                    $match = $this->matchCronExpression($e[0], $d['minutes']) &&
                            $this->matchCronExpression($e[1], $d['hours']) &&
                            $this->matchCronExpression($e[2], $d['mday']) &&
                            $this->matchCronExpression($e[3], $d['mon']) &&
                            $this->matchCronExpression($e[4], $d['wday']);
                    if ($match) {
                        try {
                            $model = new Model([
                                'code' => $config['code'],
                                'scheduled_at' => date('Y-m-d h:i:s', $ts)
                            ]);
                            $model->save();
                            $count ++;
                        } catch (Exception $e) {
                            
                        }
                    }
                }
                if ($count >= 30) {
                    break;
                }
            }
        }
        return $this;
    }

    protected function execJob()
    {
        $collection = new Collection;
        $collection->where(['status' => 0])->where(['status' => 2], 'OR');
        $collection->load(false);
        if (count($collection)) {
            foreach ($collection as $item) {
                $ts = time();
                $time = date('Y-m-d h:i:s', $ts);
                $model = new Model([
                    'id' => $item->offsetGet('id')
                ]);
                if ($item->offsetGet('status') == 2) {
                    $model->offsetSet('status', 4)
                            ->save();
                    continue;
                }
                if (strtotime($item->offsetGet('scheduled_at')) + 1800 < $ts) {
                    $model->offsetSet('status', 3)
                            ->save();
                    continue;
                }
                $model->setData([
                    'executed_at' => $time,
                    'status' => 2,
                ]);
                $model->save();
                $code = exclude('::', $time['code']);
                try {
                    $class = new $code[0];
                    $class->{$code[1]}();
                    $model->setData([
                        'finished_at' => date('Y-m-d h:i:s'),
                        'status' => 1
                    ]);
                } catch (Error $e) {
                    $model->setData([
                        'messages' => $e->getMessage(),
                        'status' => 5
                    ]);
                } catch (Exception $e) {
                    $model->setData([
                        'messages' => $e->getMessage(),
                        'status' => 5
                    ]);
                }
                $model->save();
            }
        }
        return $this;
    }

    public function run()
    {
        $this->addJob()->execJob();
    }

    protected function usageHelp()
    {
        return <<<USAGE
Usage:  php -f script.php
        
        Add code into crontab
        You can add '>/dev/null 2>&1' to forbid output
        
Status:
    0: Not executed
    1: Finished
    2: Running
    3: Expired
    4: Timeout
    5: Exception
        
USAGE;
    }

}

new Cron;
