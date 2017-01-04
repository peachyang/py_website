<?php

namespace Seahinet\Retailer\ViewModel\Dashboard;

use Seahinet\Catalog\Model\Collection\Product;
use Seahinet\Retailer\ViewModel\AbstractViewModel;
use Seahinet\Sales\Model\Collection\Order\Item;
use Zend\Db\Sql\Expression;

class Ranking extends AbstractViewModel
{

    public function getItems($limit = 3)
    {
        $items = new Item;
        $items->columns(['product_id', 'sum' => new Expression('sum(qty)')])
                ->join('sales_order', 'sales_order.id=sales_order_item.order_id', [], 'left')
                ->where(['sales_order.store_id' => $this->getRetailer()->offsetGet('store_id')])
                ->group('product_id')
                ->order('sum DESC')
                ->limit($limit);
        $ids = [];
        $result = [];
        $items = $items->load(true, true);
        if (count($items)) {
            foreach ($items->toArray() as $item) {
                $ids[$item['product_id']] = $item['sum'];
            }
            $products = new Product;
            $products->where(['id' => array_keys($ids)]);
            $products = $products->load(true, true)->toArray();
            foreach ($ids as $id => $qty) {
                foreach ($products as $key => $product) {
                    if ($product->offsetGet('id') == $id) {
                        $product->offsetSet('qty', $qty);
                        $result[] = $product;
                        unset($products[$key]);
                    }
                }
            }
        }
        return $result;
    }

}
