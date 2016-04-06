<?php

namespace Seahinet\Admin\Model;

use Seahinet\Admin\Model\Collection\Role as Collection;
use Seahinet\Lib\Model\AbstractModel;
use Zend\Permissions\Rbac\Role as RbacRole;

class Role extends AbstractModel
{

    protected $role = null;

    protected function _construct()
    {
        $this->init('admin_role', 'id', ['id', 'parent_id', 'name', 'status']);
    }

    protected function hasPermission($name)
    {
        return !is_null($this->role) && $this->role->hasPermission('ALL') || $this->role->hasPermission($name);
    }

    protected function afterLoad()
    {
        $cache = $this->getContainer()->get('cache');
        $this->role = $cache->fetch('RBAC_ROLE_' . $this->offsetGet('name'));
        if (!$this->role) {
            $this->role = new RbacRole($this->offsetGet('name'));
            $roles = new Collection;
            $roles->load();
            $children = [];
            foreach ($roles as $role) {
                if ($role['id'] == $this->getId()) {
                    foreach ($role['operation'] as $operation) {
                        $this->role->addPermission($operation);
                    }
                } else {
                    if (!isset($children[$role['parent_id']])) {
                        $children[$role['parent_id']] = [];
                    }
                    $children[$role['parent_id']][] = $role;
                }
            }
            $this->addChildren($children, $this->role, $this->getId());
            $cache->save('RBAC_ROLE_' . $this->offsetGet('name'), $this->role);
        }
        parent::afterLoad();
    }

    protected function addChildren($children, $parent, $pid)
    {
        if (isset($children[$pid])) {
            foreach ($children[$pid] as $child) {
                $role = new RbacRole($child['name']);
                foreach ($child['operation'] as $operation) {
                    $role->addPermission($operation);
                }
                $parent->addChild($this->addChildren($children, $role, $role['id']));
            }
        }
        return $parent;
    }

}
