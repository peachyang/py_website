<?php

namespace Seahinet\Article\ViewModel\Category;

use Seahinet\Article\Model\Category;
use Seahinet\Article\ViewModel\Product\Price;
use Seahinet\Lib\ViewModel\Template;

class ProductList extends Template
{

    protected $products = null;
    protected $urls = [];
    protected $indexer = null;

    public function getTemplate()
    {
        if (!$this->template) {
            return 'article/product/list/' . $this->getQuery('mode', 'grid');
        }
        return parent::getTemplate();
    }

    public function getCategory()
    {
        return $this->getVariable('category', null);
    }

    public function setCategory(Category $category)
    {
        $this->variables['category'] = $category;
        return $this;
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function setProducts($products)
    {
        $this->products = $products;
        return $this;
    }

    public function getProductColor()
    {
        $color = new Color;
        $color->setVariable('product', $product);
        return $color;
    }

    public function getPriceBox($product)
    {
        $box = new Price;
        $box->setVariable('product', $product);
        return $box;
    }

}
