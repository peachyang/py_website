<?php

namespace Seahinet\Lib\Model;

class Increment extends AbstractModel
{

    protected function construct()
    {
        $this->init('core_increment', 'type', ['type', 'store_id', 'prefix', 'last_id']);
    }

    public function getIncrementId($length)
    {
        if (!$this->isLoaded) {
            return '';
        }
        return $this->storage['prefix'] . sprintf('%' . $length . 'd', $this->storage['last_id']);
    }

}
