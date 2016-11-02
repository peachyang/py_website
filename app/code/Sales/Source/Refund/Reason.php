<?php

namespace Seahinet\Sales\Source\Refund;

use Seahinet\Lib\Source\SourceInterface;

class Reason implements SourceInterface
{

    public function getSourceArray()
    {
        return [
            'I don\'t want it',
            'Quality issues',
            'Short delivered',
            'Not coincide to the description',
            'Invoice issues'
        ];
    }

}
