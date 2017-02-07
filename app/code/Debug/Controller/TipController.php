<?php

namespace Seahinet\Debug\Controller;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Session\Segment;

class TipController extends ActionController
{

    public function switchAction()
    {
        if (!Bootstrap::isDeveloperMode()) {
            return $this->notFoundAction();
        }
        $segment = new Segment('debug');
        $segment->set('tip', !$segment->get('tip', false));
        return $this->response(['error' => 0, 'message' => [], 'reload' => 1], '', 'core');
    }

}
