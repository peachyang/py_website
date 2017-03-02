<?php

namespace Seahinet\Lib\Model;

class Increment extends AbstractModel
{

    protected function construct()
    {
        $this->init('core_increment', 'type', ['type', 'store_id', 'prefix', 'last_id']);
    }

    public function getIncrementId($length = '')
    {
        if (!$this->isLoaded) {
            return '';
        }
        $this->storage['last_id'] ++;
        $this->save();
        return $this->storage['prefix'] . sprintf('%0' . $length . 'd', $this->storage['last_id']);
    }

}
