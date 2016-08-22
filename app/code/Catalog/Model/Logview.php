<?php

namespace Seahinet\Catalog\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Catalog\Model\Collection\Logview as Collection;
use Seahinet\Resource\Model\Resource;
use Seahinet\Catalog\Model\Product;

class Logview extends AbstractModel
{

    use \Seahinet\Lib\Traits\Url;

    protected function construct()
    {
        $this->init('log_view', 'customer_id,product_id', ['customer_id', 'product_id', 'created_at', 'updated_at']);
    }

    public function getThumbnail()
    {
        if (!empty($this->storage['thumbnail'])) {
            $resource = new Resource;
            $resource->load($this->storage['thumbnail']);
            return $resource['real_name'];
        }
        return $this->getPubUrl('frontend/images/placeholder.png');
    }

}
