<?php

namespace Seahinet\Admin\ViewModel;

use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\ViewModel\AbstractViewModel;

class Header extends AbstractViewModel
{

    public function getUsername()
    {
        $segment = new Segment('admin');
        return $segment->get('user')['username'];
    }

}
