<?php

namespace Seahinet\Resource\Model;

use Seahinet\Lib\Model\AbstractModel;

class Resource extends AbstractModel
{

    use \Seahinet\Resource\Traits\Remove\Local,
        \Seahinet\Resource\Traits\Upload\Local;

    public static $options = [
        'path' => 'pub/resource/',
        'dir_mode' => 0755
    ];

    protected function construct()
    {
        $this->init('resource', 'id', ['id', 'store_id', 'real_name', 'uploaded_name', 'md5', 'file_type', 'category_id', 'size', 'sort_order']);
    }

}
