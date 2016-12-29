<?php

namespace Seahinet\AccountBalance\Controller;

use Seahinet\Lib\Controller\ActionController;

class IndexController extends ActionController
{

    public function IndexAction()
    {
        return $this->getLayout('');
    }

}
