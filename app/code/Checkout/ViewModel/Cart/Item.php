<?php

namespace Seahinet\Checkout\ViewModel\Cart;

use Seahinet\Lib\ViewModel\Template;

class Item extends Template
{

    protected static $currency = null;

    public function getCurrency()
    {
        return $this->getContainer()->get('currency');
    }

    public function getOption($product_mini = null){
        $product = $product_mini ? $product_mini : $this->getVariable('item');
        $options = $product['product']->getOptions()->toArray();
        if ($options){
            $options_array = [];
            foreach ($options as $item){
                $value = [];
                foreach ($item['value'] as $val){
                    $value[$val['id']] = $val;
                }
                $options_array[$item['id']] = ['title' => $item['title'],'value' => $value];
            }
            $options_select = [];
            foreach (json_decode($product['options']) as $k=>$v){
                $options_select[$options_array[$k]['title']] = isset($options_array[$k]['value'][$v]) ? $options_array[$k]['value'][$v]['title'] : $v;
            }
        }else {
            return [];
        }
        return $options_select;
    }
}
