<?php

namespace Seahinet\Sales\Source;

use Seahinet\Lib\Source\SourceInterface;

class RefundReason implements SourceInterface
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
