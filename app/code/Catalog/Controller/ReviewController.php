<?php

namespace Seahinet\Catalog\Controller;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Controller\ActionController;
use Seahinet\Catalog\Model\Product\Review;

class ReviewController extends ActionController
{

    use \Seahinet\Catalog\Traits\Breadcrumb;

    public function saveAction()
    {
         if ($this->getRequest()->isPost()) {
             $data = $this->getRequest()->getPost();
             $data['status'] = 1;
             $data['language_id'] = Bootstrap::getLanguage()->getId();
             (new Review($data))->save();
             echo"<script>alert('".$this->translate('Submitted Successfully')."');history.go(-1);</script>";
         }
    }

}
