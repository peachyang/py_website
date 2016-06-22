<?php

namespace Seahinet\Email\Source;

use Seahinet\Email\Model\Collection\Template as Collection;
use Seahinet\Lib\Source\SourceInterface;

class Template implements SourceInterface
{

    public function getSourceArray()
    {
        $collection = new Collection;
        $collection->columns(['code', 'subject']);
        $result = [];
        foreach ($collection as $item) {
            $result[$item['code']] = $item['subject'];
        }
        return $result;
    }

}
