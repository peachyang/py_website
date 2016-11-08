<?php

namespace Seahinet\Catalog\Model\Product;

use Seahinet\Catalog\Model\Product as Model;
use Seahinet\Catalog\Model\Collection\Product\Rating;
use Seahinet\Lib\Exception\SpamException;
use Seahinet\Lib\Model\AbstractModel;

class Review extends AbstractModel
{

    protected function construct()
    {
        $this->init('review', 'id', ['id', 'product_id', 'customer_id', 'order_id', 'language_id', 'subject', 'content', 'reply', 'images', 'anonymous', 'status']);
    }

    protected function beforeSave()
    {
        $this->beginTransaction();
        if (!empty($this->storage['content'])) {
            if ($this->getContainer()->get('akismet')->isSpam($this->storage['content'])) {
                throw new SpamException;
            }
            $this->storage['content'] = gzencode($this->storage['content']);
        }
        if (!empty($this->storage['reply'])) {
            $this->storage['reply'] = gzencode($this->storage['reply']);
        }
        parent::beforeSave();
    }

    protected function afterSave()
    {
        if (!empty($this->storage['rating'])) {
            $tableGateway = $this->getTableGateway('review_rating');
            foreach ((array) $this->storage['rating'] as $id => $value) {
                $this->upsert(['value' => $value], ['review_id' => $this->getId(), 'rating_id' => $id], $tableGateway);
            }
            $this->flushList('rating');
        }
        parent::afterSave();
        $this->commit();
    }

    protected function afterLoad(&$result)
    {
        if (isset($result[0])) {
            $data = @gzdecode($result[0]['content']);
            if ($data !== false) {
                $result[0]['content'] = $data;
            }
            $data = @gzdecode($result[0]['reply']);
            if ($data !== false) {
                $result[0]['reply'] = $data;
            }
        }
        parent::afterLoad($result);
    }

    public function getProduct()
    {
        if (!empty($this->storage['product_id'])) {
            $product = new Model;
            $product->load($this->storage['product_id']);
            return $product;
        }
        return null;
    }

    public function getRatings()
    {
        if ($this->getId()) {
            $collection = new Rating;
            $collection->join('review_rating', 'review_rating.rating_id=rating.id', ['value'], 'left')
                    ->where([
                        'status' => 1,
                        'review_rating.review_id' => $this->getId()
            ]);
            return $collection;
        }
        return [];
    }

}
