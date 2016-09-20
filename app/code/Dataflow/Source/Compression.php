<?php

namespace Seahinet\Dataflow\Source;

use Seahinet\Lib\Source\SourceInterface;

class Compression implements SourceInterface
{

    public function getSourceArray()
    {
        $result = ['Uncompressed'];
        if (extension_loaded('zlib')) {
            $result['gz'] = 'GZip';
        }
        if (extension_loaded('bz2')) {
            $result['bz2'] = 'BZip2';
        }
        return $result;
    }

}
