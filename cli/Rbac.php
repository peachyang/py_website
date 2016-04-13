<?php

namespace Seahinet\Cli;

require __DIR__ . '/../app/bootstrap.php';

use Exception;
use ReflectionClass;
use Seahinet\Admin\Model\Operation;
use Symfony\Component\Finder\Finder;

class Rbac extends AbstractCli
{

    public function run()
    {
        if (isset($this->args['generate']) || isset($this->args['g'])) {
            $finder = new Finder;
            $finder->files()->in(BP . 'app/code')->path('/Controller/')->name('*Controller.php');
            $count = 1;
            try {
                $model = new Operation;
                $model->delete(['is_system' => 1]);
                $model->setData([
                    'id' => -1,
                    'name' => 'ALL',
                    'is_system' => 1
                ])->save();
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            foreach ($finder as $file) {
                $className = 'Seahinet\\' . $file->getRelativePath() . '\\' . str_replace('.php', '', $file->getFilename());
                $reflection = new ReflectionClass($className);
                if ($reflection->isSubclassOf('Seahinet\\Lib\\Controller\\AuthActionController')) {
                    foreach ($reflection->getMethods() as $method) {
                        if ($method->isPublic() && substr($method->getName(), -6) === 'Action') {
                            try {
                                $model = new Operation;
                                $model->setData([
                                    'name' => $className . '::' . $method->getName(),
                                    'is_system' => 1
                                ])->save();
                                $count++;
                            } catch (Exception $e) {
                                echo $e->getMessage();
                            }
                        }
                    }
                }
            }
            echo $count ? $count . ' item(s) have been generated successfully.' : 'No item has been generated.';
        } else {
            echo $this->usageHelp();
        }
    }

    protected function usageHelp()
    {
        return <<<USAGE
Usage:  php -f script.php -- [options]

    help|-h           Help
    generate|-g       Generate role operation

USAGE;
    }

}

new Rbac;
