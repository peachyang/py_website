<?php

namespace Seahinet\Customer\Model;

use Seahinet\Lib\Model\Eav\Entity;
use Seahinet\Lib\Session\Segment;
use Zend\Crypt\Password\Bcrypt;

class Customer extends Entity
{

    const ENTITY_TYPE = 'customer';

    protected function construct()
    {
        $this->init('id', ['id', 'type_id', 'attribute_set_id', 'store_id', 'language_id', 'increment_id', 'open_id', 'status']);
    }

    public function __clone()
    {
        $storage = [
            'id' => $this->storage['id'],
            'store_id' => $this->storage['store_id'],
            'language_id' => $this->storage['language_id'],
            'increment_id' => $this->storage['increment_id'],
            'username' => $this->storage['username']
        ];
        $this->storage = $storage;
        $this->isLoaded = false;
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

    public function login($username, $password)
    {
        if ($this->valid($username, $password)) {
            $segment = new Segment('customer');
            $segment->set('isLoggedin', true)
                    ->set('customer', clone $this);
            return true;
        }
        return false;
    }

    public function valid($username, $password)
    {
        if (!$this->isLoaded) {
            $this->load($username, 'username');
        } else if ($this->storage['username'] !== $username) {
            $this->isLoaded = false;
            $this->load($username, 'username');
        }
        return $this->offsetGet('status') && (new Bcrypt)->verify($password, $this->offsetGet('password'));
    }

}
