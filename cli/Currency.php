<?php

namespace Seahinet\Cli;

require __DIR__ . '/../app/bootstrap.php';

use Seahinet\I18n\Listeners\Currency as Listener;

class Currency extends AbstractCli
{

    public function run()
    {
        if (isset($this->args['sync']) || isset($this->args['s'])) {
            $listener = new Listener;
            echo $listener->schedule();
        } else {
            echo $this->usageHelp();
        }
    }

    protected function usageHelp()
    {
        return <<<'USAGE'
Usage:  php -f script.php -- [options]

    help|-h           Help
    sync|-s           Synchronize currency rate

USAGE;
    }

}

new Currency;
