<?php

namespace Seahinet\Admin\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Lib\Session\Segment;
use Zend\Crypt\Password\Bcrypt;

/**
 * System backend user
 */
class User extends AbstractModel
{

    protected $role = null;

    protected function _construct()
    {
        $this->init('admin_user', 'id', ['id', 'role_id', 'status', 'username', 'password', 'email', 'logdate', 'lognum', 'rp_token', 'rp_token_created_at']);
    }

    public function __clone()
    {
        $storage = [
            'id' => $this->storage['id'],
            'role_id' => $this->storage['role_id'],
            'username' => $this->storage['username'],
            'email' => $this->storage['email']
        ];
        $this->storage = $storage;
        $this->isLoaded = false;
    }

    public function login($username, $password)
    {
        if ($this->valid($username, $password)) {
            $segment = new Segment('admin');
            $segment->set('isLoggedin', true)
                    ->set('user', clone $this);
            return true;
        }
        return false;
    }

    public function valid($username, $password)
    {
        if (!$this->isLoaded) {
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
