<?php

namespace Seahinet\Cli;

use Seahinet\Lib\Bootstrap;

abstract class AbstractCli
{

    protected $args = [];

    public function __construct()
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            die(1);
        }
        Bootstrap::init($_SERVER);
        $this->parseArgs();
        $this->showHelp();
        $this->run();
    }

    protected function parseArgs()
    {
        $current = null;
        foreach ($_SERVER['argv'] as $arg) {
            $match = array();
            if (preg_match('#^--([\w\d_-]{1,})$#', $arg, $match) || preg_match('#^-([\w\d_]{1,})$#', $arg, $match)) {
                $current = $match[1];
                $this->args[$current] = true;
            } else {
                if ($current) {
                    $this->args[$current] = $arg;
                } else if (preg_match('#^([\w\d_]{1,})$#', $arg, $match)) {
                    $this->args[$match[1]] = true;
                }
            }
        }
        return $this;
    }

    abstract public function run();

    protected function showHelp()
    {
        if (isset($this->args['h']) || isset($this->args['help'])) {
            echo $this->usageHelp();
            die(0);
        }
    }
    
    protected function usageHelp(){
        return <<<'USAGE'
Usage:  php -f script.php -- [options]

    --help -h           Help
USAGE;
    }

}
