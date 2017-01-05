<?php

namespace Seahinet\Admin\ViewModel\Promotion\Edit;

use Seahinet\Admin\ViewModel\Edit;
use Seahinet\Promotion\Model\Collection\Handler as Collection;

class Action extends Edit
{

    public function getOptions($source)
    {
        if (is_subclass_of($source, '\\Seahinet\\Lib\\Source\\SourceInterface')) {
            return (new $source)->getSourceArray();
        }
        return [];
    }

    public function getCollection()
    {
        if ($this->getQuery('id')) {
            $collection = new Collection;
            $collection->where(['promotion_id' => $this->getQuery('id')]);
            $result = [];
            foreach ($collection as $item) {
                if (!isset($result[(int) $item->offsetGet('parent_id')])) {
                    $result[(int) $item->offsetGet('parent_id')] = [];
                }
                $result[(int) $item->offsetGet('parent_id')][] = $item->toArray();
            }
            return $result;
        }
        return [];
    }

    protected function prepareElements($columns = [])
    {
        $columns = [
            'apply_to' => [
                'type' => 'select',
                'label' => 'Apply to',
                'required' => 'required',
                'options' => ['Whole Cart', 'Item(s) Only']
            ],
            'is_fixed' => [
                'type' => 'select',
                'label' => 'Discount Calculation Method',
                'required' => 'required',
                'options' => [1 => 'Reduce the amount of fixed', 0 => 'Price is calculated in percentage']
            ],
            'price' => [
                'type' => 'price',
                'label' => 'Discount for Each Product',
                'required' => 'required'
            ],
            'qty' => [
                'type' => 'number',
                'label' => 'Maximum Qty Discount is Applied To',
                'attrs' => [
                    'min' => 0
                ]
            ],
            'free_shipping' => [
                'type' => 'select',
                'label' => 'Free Shipping',
                'required' => 'required',
                'options' => ['No', 'Yes']
            ],
            'stop_processing' => [
                'type' => 'select',
                'label' => 'Stop low Priority',
                'required' => 'required',
                'options' => ['No', 'Yes']
            ]
        ];
        return parent::prepareElements($columns);
    }

}
