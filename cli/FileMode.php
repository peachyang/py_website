<?php

namespace Seahinet\Cli;

require __DIR__ . '/../app/bootstrap.php';

use Symfony\Component\Finder\Finder;

class FileMode extends AbstractCli
{

    public function run()
    {
        $finder = new Finder;
        $finder->files()->in(BP);
        foreach ($finder as $file) {
            chmod($file->getRealPath(), 0755);
        }
        $finder->directories()->in(BP);
        foreach ($finder as $dir) {
            chmod($dir->getRealPath(), 0644);
        }
    }

    protected function usageHelp()
    {
        return <<<'USAGE'
Usage:  php -f script.php -- [options]

    Not effected on Windows
USAGE;
    }

}

new FileMode;