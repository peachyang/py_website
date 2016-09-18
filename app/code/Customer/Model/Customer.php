<?php

namespace Seahinet\Customer\Model;

use Seahinet\Customer\Model\Collection\Group;
use Seahinet\Lib\Model\Eav\Entity;
use Seahinet\Lib\Session\Segment;
use Zend\Crypt\Password\Bcrypt;
use Seahinet\Lib\Model\Store;
use Zend\Db\TableGateway\TableGateway;

class Customer extends Entity
{

    const ENTITY_TYPE = 'customer';

    protected function construct()
    {
        $this->init('id', ['id', 'type_id', 'attribute_set_id', 'store_id', 'language_id', 'increment_id', 'open_id', 'confirm_token', 'confirm_token_created_at', 'status']);
    }

    public function __clone()
    {
        $storage = [
            'id' => $this->storage['id'],
            'store_id' => $this->storage['store_id'],
            'language_id' => $this->storage['language_id'],
            'increment_id' => $this->storage['increment_id'],
            'username' => $this->storage['username'],
            'password' => $this->storage['password']
        ];
        $this->storage = $storage;
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
            $tableGateway = new TableGateway('customer_in_group', $this->getContainer()->get('dbAdapter'));
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
        if (!$this->isLoaded) {
            $this->load($username, 'username');
        } else if ($this->storage['username'] !== $username) {
            $this->isLoaded = false;
            $this->load($username, 'username');
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
