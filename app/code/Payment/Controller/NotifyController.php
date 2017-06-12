<?php

namespace Seahinet\Payment\Controller;

use Seahinet\Lib\Controller\ActionController;
use Seahinet\Log\Model\Payment;

class NotifyController extends ActionController
{

    protected $tradeIndex = [
        'out_trade_no'
    ];

    public function indexAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if (is_object($data)) {
                $data = (array) $data;
            }
            $tradeId = false;
            foreach ($this->tradeIndex as $index) {
                if (!empty($data[$index])) {
                    $tradeId = $data[$index];
                    break;
                }
            }
            if ($tradeId) {
                $log = new Payment;
                $log->load($tradeId, 'trade_id');
                $method = new $log['method'];
                $response = $method->asyncNotice($data);
                if ($response !== false) {
                    return $response;
                }
            }
        }
        return $this->notFoundAction();
    }

}
