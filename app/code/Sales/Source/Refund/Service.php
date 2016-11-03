<?php

namespace Seahinet\Sales\Source\Refund;

use Seahinet\Lib\Source\SourceInterface;

class Service implements SourceInterface
{

    public function getSourceArray()
    {
        return [
            'Refund Only',
            'Return &amp; Refund',
            'Repair or Exchange'
        ];
    }

}
