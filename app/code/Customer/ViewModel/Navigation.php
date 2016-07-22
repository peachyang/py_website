<?php

namespace Seahinet\Customer\ViewModel;

use Seahinet\Lib\Stdlib\Singleton;
use Seahinet\Lib\ViewModel\Template;

class Navigation extends Template implements Singleton
{

    protected static $instance = null;
    protected $links = [];
    protected $group = [];

    protected function __construct()
    {
        $this->setTemplate('customer/navigation');
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
            if (!isset($a['priority'])) {
                $a['priority'] = 0;
            }
            if (!isset($b['priority'])) {
                $b['priority'] = 0;
            }
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

}
