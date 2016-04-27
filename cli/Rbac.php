<?php

namespace Seahinet\Cli;

require __DIR__ . '/../app/bootstrap.php';

use Exception;
use ReflectionClass;
use Seahinet\Admin\Model\Operation;
use Symfony\Component\Finder\Finder;
use Zend\Db\Adapter\Exception\InvalidQueryException;

class Rbac extends AbstractCli
{

    use \Seahinet\Lib\Traits\DB;

    public function run()
    {
        if (isset($this->args['generate']) || isset($this->args['g'])) {
            $finder = new Finder;
            $finder->files()->in(BP . 'app/code')->path('/Controller/')->name('*Controller.php');
            $count = 1;
            try {
                $model = new Operation;
                $model->setData([
                    'id' => -1,
                    'name' => 'ALL',
                    'is_system' => 1
                ])->save([], true);
            } catch (InvalidQueryException $e) {
                $count = 0;
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            foreach ($finder as $file) {
                $className = str_replace(DS, '\\', trim($file->getRelativePath(), DS)) . '\\' . str_replace('.php', '', $file->getFilename());
                $reflection = new ReflectionClass('Seahinet\\' . $className);
                if ($reflection->isSubclassOf('Seahinet\\Lib\\Controller\\AuthActionController')) {
                    $operation = preg_replace('/Controller(?:\\\\)?/','',$className) . '::';
                    foreach ($reflection->getMethods() as $method) {
                        if ($method->isPublic() && substr($method->getName(), -6) === 'Action' && $method->getName() !== 'notFoundAction') {
                            try {
                                $model = new Operation;
                                $model->setData([
                                    'name' => $operation . str_replace('Action','',$method->getName()),
                                    'is_system' => 1
                                ])->save();
                                $count++;
                            } catch (InvalidQueryException $e) {
                                
                            } catch (Exception $e) {
                                echo $e->getMessage();
                            }
                        }
                    }
                }
            }
            echo ($count ? $count . ' item(s) have been generated successfully.' : 'No item has been generated.') . chr(10);
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
