<?php

namespace Seahinet\Retailer\ViewModel;

use Seahinet\Lib\Stdlib\Singleton;
use Seahinet\Lib\ViewModel\Template;

class Navigation extends Template implements Singleton
{

    protected static $instance = null;
    protected $links = [];
    protected $groups = [];

    protected function __construct()
    {
        $this->setTemplate('retailer/navigation');
    }

    public static function instance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    public function getLinks()
    {
        uasort($this->links, function($a, $b) {
            $a['priority'] = (!isset($a['priority'])) ? 0 : $a['priority'];
            $b['priority'] = (!isset($b['priority'])) ? 0 : $b['priority'];
            return $a['priority'] === $b['priority'] ? 0 : ($a['priority'] > $b['priority'] ? 1 : -1);
        });
        return $this->links;
    }

    public function setLinks(array $links)
    {
        $this->links = $links;
        return $this;
    }

    public function addLink(array $link)
    {
        $this->links[] = $link;
        return $this;
    }

    public function addGroup(array $group)
    {
        $this->groups[] = $group;
        return $this;
    }

    public function getGroups()
    {
        uasort($this->groups, function($a, $b) {
            $a['priority'] = (!isset($a['priority'])) ? 0 : $a['priority'];
            $b['priority'] = (!isset($b['priority'])) ? 0 : $b['priority'];
            return $a['priority'] === $b['priority'] ? 0 : ($a['priority'] > $b['priority'] ? 1 : -1);
        });
        return $this->groups;
    }

}
