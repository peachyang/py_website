<?php

namespace Seahinet\Shipping\Source;

use Seahinet\Lib\Source\SourceInterface;

class Carrier implements SourceInterface
{

    public function getSourceArray()
    {
        return [
            'dhl' => 'DHL',
            'fedex' => 'Federal Express',
            'shunfeng' => 'S.F. Express',
            'tnt' => 'TNT',
            'ups' => 'United Parcel Service',
            'usps' => 'United States Postal Service',
        ];
    }

}
