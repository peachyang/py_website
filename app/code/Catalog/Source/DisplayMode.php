<?php

namespace Seahinet\Catalog\Source;

use Seahinet\Lib\Source\SourceInterface;

class DisplayMode implements SourceInterface
{

    public function getSourceArray()
    {
        return [
            'Products only', 'Static block only', 'Static block and products'
        ];
    }

}
