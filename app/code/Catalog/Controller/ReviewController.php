<?php

namespace Seahinet\Catalog\Controller;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Controller\ActionController;
use Seahinet\Catalog\Model\Product\Review;
use TijsVerkoyen\Akismet\Exception;

class ReviewController extends ActionController
{

    public function saveAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $data['status'] = 1;
            $data['language_id'] = Bootstrap::getLanguage()->getId();
            $review = new Review($data);
            try {
                if (!$this->getContainer()->get('akismet')->isSpam($data['content'])) {
                    $review->save();
                }
            } catch (Exception $e) {
                $review->setData('status', 0)->save();
            }
            echo"<script>alert('" . $this->translate('Submitted Successfully') . "');history.go(-1);</script>";
        }
    }

}
