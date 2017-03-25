<?php

namespace Seahinet\Customer\Model;

use Seahinet\Customer\Model\Collection\Group;
use Seahinet\Lib\Model\Eav\Entity;
use Seahinet\Lib\Model\Store;
use Seahinet\Lib\Session\Segment;
use Zend\Crypt\Password\Bcrypt;

class Customer extends Entity
{

    const ENTITY_TYPE = 'customer';

    public static $attr4Login = [
        'username'
    ];

    protected function construct()
    {
        $this->init('id', ['id', 'type_id', 'attribute_set_id', 'store_id', 'language_id', 'increment_id', 'confirm_token', 'confirm_token_created_at', 'status']);
    }

    public function __clone()
    {
        $this->storage = array_diff_key($this->storage, ['password', 'confirm_token', 'confirm_token_created_at']);
        $this->isLoaded = false;
    }

    protected function beforeSave()
    {
        if (isset($this->storage['password']) && strpos($this->storage['password'], '$2y$') !== 0) {
            $this->storage['password'] = (new Bcrypt)->create($this->storage['password']);
        }
        parent::beforeSave();
    }

    protected function afterSave()
    {
        parent::afterSave();
        if (isset($this->storage['group_id'])) {
            $tableGateway = $this->getTableGateway('customer_in_group');
            $tableGateway->delete(['customer_id' => $this->getId()]);
            $groups = is_string($this->storage['group_id']) ? explode(',', $this->storage['group_id']) : (array) $this->storage['group_id'];
            foreach ($groups as $id) {
                $tableGateway->insert(['group_id' => $id, 'customer_id' => $this->getId()]);
            }
        }
    }

    public function login($username, $password)
    {
        if ($this->valid($username, $password)) {
            $segment = new Segment('customer');
            $segment->set('hasLoggedIn', true)
                    ->set('customer', clone $this);
            $this->getEventDispatcher()->trigger('customer.login.after', ['model' => $this]);
            return true;
        }
        return false;
    }

    public function valid($username, $password)
    {
        foreach (static::$attr4Login as $attr) {
            if (!$this->isLoaded) {
                $this->load($username, $attr);
            } else if ($this->storage[$attr] !== $username) {
                $this->isLoaded = false;
                $this->load($username, $attr);
            }
            if ($this->getId()) {
                break;
            }
        }
        return $this->offsetGet('status') && (new Bcrypt)->verify($password, $this->offsetGet('password'));
    }

    public function getGroup()
    {
        if ($this->getId()) {
            $groups = new Group;
            $groups->join('customer_in_group', 'customer_in_group.group_id=customer_group.id', [], 'left')
                    ->where(['customer_in_group.customer_id' => $this->getId()]);
            return $groups;
        }
        return [];
    }

    public function getLevel()
    {
        if (empty($this->storage['level'])) {
            $this->getEventDispatcher()->trigger('customer.level.calc', ['customer' => $this]);
        } else {
            $this->storage['level'] = (new Level)->load($this->storage['level']);
        }
        return empty($this->storage['level']) ? 0 : $this->storage['level']->getName();
    }

    public function getBalance()
    {
        if (empty($this->storage['balance'])) {
            $this->getEventDispatcher()->trigger('customer.balance.calc', ['customer' => $this]);
        }
        return empty($this->storage['balance']) ? 0 : $this->storage['balance'];
    }

    public function getStore()
    {
        if (is_null($this->store) && $this->offsetGet('store_id')) {
            $store = new Store;
            $store->load($this->offsetGet('store_id'));
            if ($store->getId()) {
                $this->store = $store;
            }
        }
        return $this->store;
    }

}
