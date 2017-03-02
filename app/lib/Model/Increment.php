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
        $this->setData('last_id', $this->storage['last_id'] + 1)->save();
        return $this->storage['prefix'] . sprintf('%0' . $length . 'd', $this->storage['last_id']);
    }

}
