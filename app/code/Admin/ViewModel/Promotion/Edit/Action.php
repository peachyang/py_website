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
                'label' => 'Price Type',
                'required' => 'required',
                'options' => [1 => 'Fixed', 0 => 'Percent']
            ],
            'price' => [
                'type' => 'price',
                'label' => 'Price',
                'required' => 'required'
            ],
            'qty' => [
                'type' => 'number',
                'label' => 'Maximum Qty Discount is Applied To',
                'comment' => 'Available when filter is defined.'
            ],
            'free_shipping' => [
                'type' => 'select',
                'label' => 'Free Shipping',
                'required' => 'required',
                'options' => ['No', 'Yes']
            ],
            'stop_processing' => [
                'type' => 'select',
                'label' => 'Stop Processing More Rules',
                'required' => 'required',
                'options' => ['No', 'Yes']
            ]
        ];
        return parent::prepareElements($columns);
    }

}
