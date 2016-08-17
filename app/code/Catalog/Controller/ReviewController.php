<?php

namespace Seahinet\Catalog\Controller;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Controller\ActionController;
use Seahinet\Catalog\Model\Product\Review;
use TijsVerkoyen\Akismet\Exception;
use Seahinet\Lib\Session\Segment;

class ReviewController extends ActionController
{

    public function saveAction()
    {
        if ($this->getRequest()->isPost()) {
            $segment = new Segment('customer');
            if (!$this->getContainer()->get('config')['catalog/review/guests'] && !$segment->get('hasLoggedIn')){
                return $this->notFoundAction();
            }
            $data = $this->getRequest()->getPost();
            $data['status'] = (int)$this->getContainer()->get('config')['catalog/review/status'];
            $data['language_id'] = Bootstrap::getLanguage()->getId();
            $review = new Review($data);
            try {
                if (!$this->getContainer()->get('akismet')->isSpam($data['content'])) {
                    $result['error'] = 0;
                    $result['message'][] = ['message' => $this->translate('Submitted Successfully'), 'level' => 'success'];
                    $review->save();
                }
            } catch (Exception $e) {
                $result['error'] = 1;
                $result['message'][] = ['message' => $this->translate('An error detected. Please contact us or try again later.'), 'level' => 'danger'];
                $review->setData('status', 0)->save();
            }
        }else{
            $result['error'] = 1;
            $result['message'][] = ['message' => $this->translate('An error detected. Please contact us or try again later.'), 'level' => 'danger'];
        }
        return $this->response(isset($result)?$result:['error'=>0,'message'=>[]], $this->getRequest()->getHeader('HTTP_REFERER'), 'catalog');
    }

}
