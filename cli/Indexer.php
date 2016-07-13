<?php

namespace Seahinet\Cli;

require __DIR__ . '/../app/bootstrap.php';

use Seahinet\Lib\Model\Collection\Eav\Type;

/**
 * Rebuild indexer
 */
class Indexer extends AbstractCli
{

    use \Seahinet\Lib\Traits\Container;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $indexer = isset($this->args['reindex']) ? $this->args['reindex'] :
                (isset($this->args['r']) ? $this->args['r'] : false);
        if ($indexer === false) {
            echo $this->usageHelp();
        } else if ($indexer === 'all' || $indexer === true) {
            $type = new Type;
            $manager = $this->getContainer()->get('indexer');
            touch(BP . 'maintence');
            try {
                foreach ($type as $indexer) {
                    $manager->reindex($indexer['code']);
                    echo $indexer['code'], ' indexer has been rebuild successfully.', PHP_EOL;
                }
            } catch (\Exception $e) {
                echo $e->getMessage(), PHP_EOL;
            } finally {
                unlink(BP . 'maintence');
            }
        } else {
            $manager = $this->getContainer()->get('indexer');
            touch(BP . 'maintence');
            try {
                $manager->reindex($indexer);
                echo ucfirst($indexer), ' indexer has been rebuild successfully.', PHP_EOL;
            } catch (\Exception $e) {
                echo $e->getMessage(), PHP_EOL;
            } finally {
                unlink(BP . 'maintence');
            }
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
    reindex|-r        Reindex

USAGE;
    }

}

new Indexer;
