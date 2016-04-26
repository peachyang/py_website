<?php

namespace Seahinet\Admin\ViewModel\Role\Tabs;

use Seahinet\Lib\ViewModel\Root;
use Seahinet\Admin\ViewModel\Operation\Grid;
use Seahinet\Admin\Model\Collection\Operation as Collection;

class Operation extends Grid
{

    public function getModel()
    {
        return Root::instance()->getChild('edit', true)->getVariable('model');
    }

    protected function prepareCollection($collection = null)
    {
        $collection = new Collection;
        $collection->orderByRole()->load();
        $result = [[]];
        foreach ($collection as $item) {
            if ($item['role_id'] && $item['role_id'] != $this->getQuery('id')) {
                if (!isset($result[$item['role_id']])) {
                    $result[$item['role_id']] = [];
                }
                $result[$item['role_id']][] = $item;
            } else if ($item['id'] != -1) {
                $result[0][] = $item;
            }
        }
        return $result;
    }

}
