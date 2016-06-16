<?php

namespace Seahinet\Admin\ViewModel;

use Seahinet\Lib\ViewModel\AbstractViewModel;

class Dashboard extends AbstractViewModel
{

    public function getStat()
    {
        return $this->getConfig()['stat'];
    }

    public function getEvents()
    {
        $result = [];
        if (is_readable(BP . 'var/log/info.log')) {
            $handler = fopen(BP . 'var/log/info.log', 'r');
            if ($handler) {
                while (!feof($handler)) {
                    $line = trim(fgets($handler));
                    if ($line) {
                        preg_match('#^\[(?P<time>[\d\:\s\-]+)\][^\:]+\: (?P<user>[^\s]+) has (?P<operation>logged in|saved|deleted) (?P<target>[^\[]*)\[\] \[\]$#', $line, $matches);
                        $result[] = '<span class="time">' . $matches['time'] . '</span>' .
                                $this->translate('%s has ' . $matches['operation'] . ' %s', [$matches['user'], $matches['target']]);
                    }
                }
                fclose($handler);
            }
        }
        return $result;
    }

}
