<?php

namespace Seahinet\Catalog\Model\Product;

use Seahinet\Lib\Model\AbstractModel;

class Review extends AbstractModel
{

    protected function construct()
    {
        $this->init('review', 'id', ['id', 'product_id', 'customer_id', 'order_id', 'language_id', 'subject', 'content', 'status']);
    }

    protected function beforeSave()
    {
        $this->storage['content'] = gzencode($this->storage['content']);
        parent::beforeSave();
    }

    protected function afterSave()
    {
        $adapter = $this->getContainer()->get('dbAdapter');
        if (!empty($this->storage['category'])) {
            $tableGateway = new TableGateway('review', $adapter);
            $tableGateway->delete(['product_id' => $this->getId()]);
            foreach ((array) $this->storage['category'] as $category) {
                $tableGateway->insert(['product_id' => $this->getId(), 'customer_id' => $category]);
            }
        }
        parent::afterSave();
    }

    protected function afterLoad($result = array())
    {
        $data = @gzdecode($result['content']);
        if ($data !== false) {
            $result['content'] = $data;
        }
        parent::afterLoad($result);
    }
}
