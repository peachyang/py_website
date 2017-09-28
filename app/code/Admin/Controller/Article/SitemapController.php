<?php

namespace Seahinet\Admin\Controller\Catalog;

use Seahinet\Lib\Controller\AuthActionController;

class SitemapController extends AuthActionController
{

    use \Seahinet\Catalog\Traits\Sitemap;

    public function indexAction()
    {
        return $this->response($this->generate(), $this->getRequest()->getHeader('HTTP_REFERER'));
    }

}
