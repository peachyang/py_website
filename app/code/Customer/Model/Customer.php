<?php

namespace Seahinet\Customer\Model;

use Seahinet\Lib\Model\Eav\Entity;
use Zend\Crypt\Password\Bcrypt;

class Customer extends Entity
{

    const ENTITY_TYPE = 'customer';

    protected function construct()
    {
        $this->init('id', ['id', 'type_id', 'attribute_set_id', 'store_id', 'language_id', 'increment_id', 'open_id', 'status']);
    }

    protected function beforeSave()
    {
        if (isset($this->storage['password']) && strpos($this->storage['password'], '$2y$') !== 0) {
            $this->storage['password'] = (new Bcrypt)->create($this->storage['password']);
        }
        if (!isset($this->storage['id']) && !isset($this->storage['open_id'])) {
            $this->storage['open_id'] = md5(random_bytes(32) . serialize($this->storage));
        }
        parent::beforeSave();
    }

}
