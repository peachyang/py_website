<?php

namespace Seahinet\LiveChat\Controller;

use Seahinet\Lib\Controller\ActionController;

class IndexController extends ActionController
{

    public function prepareAction()
    {
        $segment = new Segment('livechat');
        if (!($id = $segment->get('id'))) {
            $data = $this->getRequest()->getPost();
            foreach ($data as $key => $value) {
                $segment->set($key, $value);
            }
            $id = md5(http_build_query($data) . $this->getContainer()->get('session')->getId());
            $segment->set('id', $id);
        }
        return $id;
    }

    public function indexAction()
    {
        return $this->getLayout('livechat');
    }

}
