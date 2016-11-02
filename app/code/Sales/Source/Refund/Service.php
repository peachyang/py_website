<?php

namespace Seahinet\Sales\Source\Refund;

use Seahinet\Lib\Source\SourceInterface;

class Service implements SourceInterface
{

    public function getSourceArray()
    {
        return [
            'Return &amp; Refund',
            'Repair or Exchange',
            'Refund Only'
        ];
    }

}
