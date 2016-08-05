<?php

namespace Seahinet\Admin\ViewModel\Sales\Grid\Cart;

use Seahinet\Admin\ViewModel\Grid;
use Seahinet\Sales\Model\Cart;
use Seahinet\Sales\Model\Collection\Cart\Item as Collection;

class Detail extends Grid
{

    protected $translateDomain = 'sales';
    protected $cart = null;

    public function getCart()
    {
        if (is_null($this->cart)) {
            $this->cart = (new Cart(['id' => $this->getQuery('id')]))->load($this->getQuery('id'));
        }
        return $this->cart;
    }

    public function getCustomer()
    {
        if ($id = $this->getCart()->offsetGet('customer_id')) {
            $customer = new Customer;
            $customer->load($id);
            return $customer;
        }
        return null;
    }

    protected function prepareCollection($collection = null)
    {
        $collection = new Collection;
        $collection->columns(['product_id', 'product_name', 'store_id', 'sku', 'options', 'qty', 'sku', 'price', 'total'])
                ->join('core_store', 'core_store.id=sales_cart_item.store_id', ['store' => 'name'])
                ->join('warehouse', 'warehouse.id=sales_cart_item.warehouse_id', ['warehouse' => 'name'])
                ->where(['cart_id' => $this->getQuery('id')]);
        return $collection;
    }

}
