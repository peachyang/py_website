<?php

namespace Seahinet\Admin\Source;

use Seahinet\Cms\Model\Collection\Role as Collection;
use Seahinet\Lib\Source\SourceInterface;

class Role implements SourceInterface
{

    public function getSourceArray($status = false)
    {
        $collection = new Collection;
        if ($status) {
            $collection->where(['admin_role.status' => 1]);
        }
        $result = [];
        foreach ($collection as $role) {
            $result[$role['id']] = $role['name'];
        }
        return $result;
    }

}
