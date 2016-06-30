<?php

namespace Seahinet\Catalog\Model\Collection\Product;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\AbstractCollection;

class Option extends AbstractCollection
{

    protected function construct()
    {
        $this->init('product_option');
    }

    public function withLabel($languageId = null)
    {
        if (is_null($languageId)) {
            $languageId = Bootstrap::getLanguage()->getId();
        } else if (is_object($languageId)) {
            $languageId = $languageId['id'];
        }
        $this->select->join('product_option_title', 'product_option_title.option_id=product_option.id', ['title'], 'left')
                ->where(['language_id' => $languageId]);
        return $this;
    }

    public function withPrice($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Bootstrap::getStore()->getId();
        } else if (is_object($storeId)) {
            $storeId = $storeId['id'];
        }
        $this->select->join('product_option_price', 'product_option_price.option_id=product_option.id', ['price', 'is_fixed'], 'left')
                ->where(['store_id' => $storeId]);
        return $this;
    }
    
}
