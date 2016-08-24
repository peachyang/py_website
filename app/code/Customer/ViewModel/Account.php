<?php

namespace Seahinet\Customer\ViewModel;

use Seahinet\Lib\ViewModel\Template;


class Account extends Template 
{
    protected $menu = [];
    protected static $currency = null;
    
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
   public function getCurrency()
    {
        if (isset($this->storage['currency'])) {
            return (new Currency)->load($this->storage['currency'], 'code');
        }
        return $this->getContainer()->get('currency');
    }
    
}