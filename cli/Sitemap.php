<?php

namespace Seahinet\Cli;

require __DIR__ . '/../app/bootstrap.php';

class Sitemap extends AbstractCli
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Lib\Traits\Url,
        \Seahinet\Lib\Traits\Translate,
        \Seahinet\Catalog\Traits\Sitemap;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if (isset($this->args['g'])) {
            $result = $this->generate(false);
            echo $result, PHP_EOL;
        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function usageHelp()
    {
        return <<<'USAGE'
Usage:  php -f script.php -- [options]

    help|-h           Help
    -g                Generate Xml Sitemap

USAGE;
    }

}

new Sitemap;
