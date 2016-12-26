<?php

namespace Seahinet\Catalog\Traits;

use Exception;
use Seahinet\Catalog\Model\Collection\Product as ProductCollection;
use Seahinet\Catalog\Model\Product;

trait Rest
{

    public function getProduct()
    {
        $data = $this->getRequest()->getQuery();
        $columns = [];
        $this->getAttributes(Product::ENTITY_TYPE)->walk(function($item) use (&$columns) {
            $columns[] = $item['code'];
        });
        if (count($columns)) {
            $products = new ProductCollection;
            $products->columns($columns);
            $this->filter($products, $data);
            $products->load(true, true);
            return $products->toArray();
        }
        return $this->getResponse()->withStatus(400);
    }

    protected function deleteProduct()
    {
        if ($this->authOptions['role_id'] === -1 &&
                count($this->getAttributes('customer', false))) {
            $id = $this->getRequest()->getQuery('id');
            if ($id) {
                $customer = new Customer;
                try {
                    $customer->setId($id)->remove();
                    return $this->getResponse()->withStatus(202);
                } catch (Exception $e) {
                    return $this->getResponse()->withStatus(400);
                }
            }
            return $this->getResponse()->withStatus(400);
        }
        return $this->getResponse()->withStatus(403);
    }

    protected function putProduct()
    {
        if ($this->authOptions['role_id'] === -1) {
            $id = $this->getRequest()->getQuery('id');
            $customer = new Customer;
            if ($id) {
                $customer->load($id);
            }
            $data = $this->getRequest()->getPost();
            $attributes = $this->getAttributes('customer', false);
            $set = [];
            foreach ($attributes as $attribute) {
                if (isset($data[$attribute['code']])) {
                    $set[$attribute['code']] = $data[$attribute['code']];
                }
            }
            try {
                if ($set) {
                    $customer->setData($set);
                    $customer->save();
                }
                return $this->getResponse()->withStatus(202);
            } catch (Exception $e) {
                return $this->getResponse()->withStatus(400);
            }
        }
        return $this->getResponse()->withStatus(403);
    }

}
