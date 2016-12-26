<?php

namespace Seahinet\Catalog\Traits;

use Exception;
use Seahinet\Catalog\Model\Collection\{
    Category as CategoryCollection,
    Product as ProductCollection
};
use Seahinet\Catalog\Model\{
    Category,
    Product
};

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
            $result = [];
            foreach ($products as $product) {
                $options = [];
                foreach ($product->getOptions()->withLabel() as $option) {
                    $options[] = (in_array($option['input'], ['select', 'radio', 'checkbox', 'multiselect']) ?
                            ['values' => $option->getValues()] : []
                            ) + $option->toArray();
                }
                $result[] = [
                    'absolute_url' => $product->getURl(),
                    'options' => $options
                        ] + $product->toArray();
            }
            return $result;
        }
        return $this->getResponse()->withStatus(400);
    }

    protected function deleteProduct()
    {
        $attributes = $this->getAttributes(Product::ENTITY_TYPE, false);
        if ($this->authOptions['role_id'] === -1 && count($attributes)) {
            $id = $this->getRequest()->getQuery('id');
            if ($id) {
                $product = new Product;
                try {
                    $product->setId($id)->remove();
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
        $attributes = $this->getAttributes(Product::ENTITY_TYPE, false);
        if ($this->authOptions['role_id'] === -1 && count($attributes)) {
            $id = $this->getRequest()->getQuery('id');
            $product = new Product;
            if ($id) {
                $product->load($id);
            }
            $data = $this->getRequest()->getPost();
            $set = [];
            foreach ($attributes as $attribute) {
                if (isset($data[$attribute['code']])) {
                    $set[$attribute['code']] = $data[$attribute['code']];
                }
            }
            try {
                if ($set) {
                    $product->setData($set);
                    $product->save();
                }
                return $this->getResponse()->withStatus(202);
            } catch (Exception $e) {
                return $this->getResponse()->withStatus(400);
            }
        }
        return $this->getResponse()->withStatus(403);
    }

    public function getCategory()
    {
        $data = $this->getRequest()->getQuery();
        $columns = [];
        $this->getAttributes(Category::ENTITY_TYPE)->walk(function($item) use (&$columns) {
            $columns[] = $item['code'];
        });
        if (count($columns)) {
            $categories = new CategoryCollection;
            $categories->columns($columns);
            $this->filter($categories, $data);
            $categories->load(true, true);
            return $categories->toArray();
        }
        return $this->getResponse()->withStatus(400);
    }

    protected function deleteCategory()
    {
        $attributes = $this->getAttributes(Category::ENTITY_TYPE, false);
        if ($this->authOptions['role_id'] === -1 && count($attributes)) {
            $id = $this->getRequest()->getQuery('id');
            if ($id) {
                $category = new Category;
                try {
                    $category->setId($id)->remove();
                    return $this->getResponse()->withStatus(202);
                } catch (Exception $e) {
                    return $this->getResponse()->withStatus(400);
                }
            }
            return $this->getResponse()->withStatus(400);
        }
        return $this->getResponse()->withStatus(403);
    }

    protected function putCategory()
    {
        $attributes = $this->getAttributes(Category::ENTITY_TYPE, false);
        if ($this->authOptions['role_id'] === -1 && count($attributes)) {
            $id = $this->getRequest()->getQuery('id');
            $category = new Category;
            if ($id) {
                $category->load($id);
            }
            $data = $this->getRequest()->getPost();
            $set = [];
            foreach ($attributes as $attribute) {
                if (isset($data[$attribute['code']])) {
                    $set[$attribute['code']] = $data[$attribute['code']];
                }
            }
            try {
                if ($set) {
                    $category->setData($set);
                    $category->save();
                }
                return $this->getResponse()->withStatus(202);
            } catch (Exception $e) {
                return $this->getResponse()->withStatus(400);
            }
        }
        return $this->getResponse()->withStatus(403);
    }

}
