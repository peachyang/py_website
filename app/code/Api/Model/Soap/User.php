<?php

namespace Seahinet\Api\Model\Soap;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Lib\Session\Segment;
use Zend\Crypt\Password\Bcrypt;

class User extends AbstractModel
{

    protected $role = null;
    protected $store = null;

    public function construct()
    {

        $this->init('api_soap_user', 'id', ['id', 'role_id', 'name', 'email', 'key']);
    }

    public function _clone()
    {
        $storage = [
            'id' => $this->storage['id'],
            'role_id' => $this->storage['role_id'],
            'name' => $this->storage['name'],
            'email' => $this->storage['email'],
            'key' => $this->storage['key']
        ];

        $this->storage = $storage;

        $this->isLoaded = FALSE;
    }

    public function login($username, $password)
    {
        if ($this->valid($username, $password)) {
            $segment = new Segment('admin');
            $segment->set('isLoggedin', TRUE)
                    ->set('user', clone $this);
            return true;
        }
        return FALSE;
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

    public function getRole()
    {
        if (is_null($this->role) && $this->offsetGet('role_id')) {
            $role = new Role;
            $role->load($this->offsetGet('role_id'));
            if ($role->getId()) {
                $this->role = $role;
            }
        }
        return $this->role;
    }

    protected function beforeSave()
    {
        if (isset($this->storage['password']) && strpos($this->storage['password'], '$2y$') !== 0) {
            $this->storage['password'] = (new Bcrypt)->create($this->storage['password']);
        }
        parent::beforeSave();
    }

}
