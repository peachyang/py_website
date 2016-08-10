<?php

namespace Seahinet\Customer\ViewModel;

use Seahinet\Lib\ViewModel\Template;

class Account extends Template 
{
    protected $menu = [];
    
    public function __construct() 
    {
        $this->getTemplate('customer/account/');
    }
    
    public function addMenu($menu)
    {
        $this->menu = $menu;
    }
    
    public function getMenu() {
        
        return  $this->menu;
    }
    
    
}