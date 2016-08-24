<?php

namespace Seahinet\Promotion\Source\Condition;

use Seahinet\Lib\Source\SourceInterface;

class Identifier implements SourceInterface
{

    public function getSourceArray()
    {
        return [
            'conbination' => 'Conbination', [
                'Product' => [
                    'attribute_set' => 'Attribute Set',
                    'category' => 'Category',
                    'product_type' => 'Product Type'
                ],
                'Item' => [
                    'price' => 'Price',
                    'qty' => 'Qty',
                    'row_total' => 'Subtotal'
                ],
                'Cart' => [
                    'subtotal' => 'Subtotal',
                    'total_qty' => 'Total Qty',
                    'total_weight' => 'Total Weight',
                    'payment_method' => 'Payment Method',
                    'shipping_method' => 'Shipping Method',
                    'postcode' => 'Postcode',
                    'county' => 'County',
                    'city' => 'City',
                    'region' => 'Region',
                    'country' => 'Country',
                ]
            ]
        ];
    }

}
