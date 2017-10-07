<?php

namespace Seahinet\Api\Source;

use Seahinet\Lib\Source\SourceInterface;

class RestValidation implements SourceInterface
{

    public function getSourceArray()
    {
        return [
            -1 => 'Administrator',
            0 => 'Anonymous',
            1 => 'Customer'
        ];
    }

}
