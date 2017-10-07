<?php

namespace Seahinet\Payment\Controller;

use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Session\Segment;
use Seahinet\Payment\Model\WeChatPay;

class WechatController extends ActionController
{

    public function indexAction()
    {
        return $this->getLayout('payment_wechat');
    }

    public function checkAction()
    {
        $segment = new Segment('payment');
        $model = new WeChatPay;
        return $this->redirect($model->check($segment->get('wechatpay')[2] ?? null) ? 'checkout/success/' : 'payment/wechat/');
    }

}
