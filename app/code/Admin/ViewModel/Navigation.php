<?php

namespace Seahinet\Admin\ViewModel;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Lib\Session\Segment;

class Navigation extends Template
{

    protected $items = [];
    protected $role;

    public function __construct()
    {
        $config = $this->getConfig();
        $this->items = $config['menu'] ?? [];
        $segment = new Segment('admin');
        $this->role = $segment->get('user')->getRole();
    }

    protected function sortItems(&$a, &$b)
    {
        if (!isset($a['priority'])) {
            $a['priority'] = 0;
        }
        if (!isset($b['priority'])) {
            $b['priority'] = 0;
        }
        if (!empty($a['children'])) {
            uasort($a['children'], [$this, 'sortItems']);
        }
        if (!empty($b['children'])) {
            uasort($b['children'], [$this, 'sortItems']);
        }
        return $a['priority'] <=> $b['priority'];
    }

    public function getMenuItems()
    {
        uasort($this->items, [$this, 'sortItems']);
        return $this->items;
    }

    public function addMenuItem(array $item)
    {
        $this->items[] = $item;
        return $this;
    }

    public function setMenuItems(array $items)
    {
        $this->items = $items;
        return $this;
    }

    public function hasPermission($operation)
    {
        if ($this->role) {
            return $this->role->hasPermission($operation);
        }
        return true;
    }

    public function getUrl($path = '')
    {
        return $this->isAdminPage() ? $this->getAdminUrl($path) : $this->getBaseUrl($path);
    }

}
