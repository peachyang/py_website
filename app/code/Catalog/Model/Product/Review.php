<?php

namespace Seahinet\Catalog\Model\Product;

use Seahinet\Lib\Model\AbstractModel;
use Zend\Db\TableGateway\TableGateway;

class Review extends AbstractModel
{

    protected function construct()
    {
        $this->init('review', 'id', ['id', 'product_id', 'customer_id', 'order_id', 'language_id', 'subject', 'content', 'status']);
    }

    protected function beforeSave()
    {
        $this->beginTransaction();
        $this->storage['content'] = gzencode($this->storage['content']);
        parent::beforeSave();
    }

    protected function afterSave()
    {
        $adapter = $this->getContainer()->get('dbAdapter');
        if (!empty($this->storage['rating'])) {
            $tableGateway = new TableGateway('review_rating', $adapter);
            foreach ((array) $this->storage['rating'] as $id => $value) {
                $this->upsert(['value' => $value], ['review_id' => $this->getId(), 'rating_id' => $id], $tableGateway);
            }
            $this->flushList('rating');
        }
        parent::afterSave();
        $this->commit();
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
