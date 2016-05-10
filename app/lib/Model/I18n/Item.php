<?php

namespace Seahinet\Lib\Model\I18n;

use Seahinet\Lib\Stdlib\ArrayObject;

class Item extends ArrayObject
{

    public function __construct($input = [])
    {
        $this->fromArray($input);
    }
    
    public function getName($locale = null)
    {
        if (is_null($locale) || !isset($this->storage['name'][$locale])) {
            return $this->storage['default_name'];
        }
        return $this->storage['name'];
    }

}
