<?php

namespace Seahinet\Checkout\ViewModel;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Lib\Session\Segment;

class Failed extends Template
{

    public function hasLoggedIn()
    {
        return (bool) (new Segment('customer'))->get('hasLoggedIn');
    }

}
