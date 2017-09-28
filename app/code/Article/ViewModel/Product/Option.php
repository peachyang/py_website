<?php

namespace Seahinet\Article\ViewModel\Product;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Lib\Session\Segment;

class Option extends Template
{

    public function getFormData()
    {
        $segment = new Segment('article');
        $data = $segment->get('form_data');
        if (isset($data['options'])) {
            $values = $data['options'];
            unset($data['options']);
            $segment->set('form_data', $data);
            return $values;
        }
        return [];
    }

    public function getOptions()
    {
        if ($product = $this->getVariable('product')) {
            return $product->getOptions();
        }
        return [];
    }

    public function getCurrency()
    {
        return $this->getContainer()->get('currency');
    }

}
