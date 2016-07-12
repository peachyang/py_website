<?php

namespace Seahinet\Catalog\ViewModel\Category;

use Seahinet\Catalog\Model\Category;
use Seahinet\Catalog\Model\Collection\Product as Collection;
use Seahinet\Catalog\Model\Product as Model;
use Seahinet\Catalog\ViewModel\Product\Price;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\ViewModel\Template;

class ProductList extends Template
{

    protected $products = null;
    protected $urls = [];
    protected $indexer = null;

    public function __construct()
    {
        $this->setTemplate('catalog/product/list/' . $this->getQuery('mode', 'grid'));
    }

    public function getCategory()
    {
        return $this->getVariable('category');
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
        if ($products instanceof Collection) {
            $this->products = [];
            $languageId = Bootstrap::getLanguage()->getId();
            foreach ($products as $product) {
                $this->products[] = new Model($languageId, $product);
            }
        } else {
            $this->products = $products;
        }
        return $this;
    }

    public function getPriceBox($product)
    {
        $box = new Price;
        $box->setVariable('product', $product);
        return $box;
    }

}
