<?php

namespace Seahinet\Admin\Controller\Article;

use Seahinet\Lib\Controller\AuthActionController;

class SitemapController extends AuthActionController
{

    use \Seahinet\Article\Traits\Sitemap;

    public function indexAction()
    {
        return $this->response($this->generate(), $this->getRequest()->getHeader('HTTP_REFERER'));
    }

}
