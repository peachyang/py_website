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
                ->group('product_id')
                ->order('sum DESC')
                ->limit($limit);
        $ids = [];
        foreach ($items->load(true, true)->toArray() as $item) {
            $ids[$item['product_id']] = $item['sum'];
        }
        $products = new Product;
        $products->where(['id' => array_keys($ids)]);
        $products = $products->toArray();
        $result = [];
        foreach ($ids as $id => $qty) {
            foreach ($products as $key => $product) {
                if ($product->offsetGet('id') == $id) {
                    $product->offsetSet('qty', $qty);
                    $result[] = $product;
                    unset($products[$key]);
                }
            }
        }
        return $result;
    }

}
