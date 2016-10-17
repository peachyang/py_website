<?php

namespace Seahinet\Payment\Controller;

use Seahinet\Lib\Controller\ActionController;

class ReturnController extends ActionController
{

    protected $tradeIndex = [
        'out_trade_no'
    ];

    public function indexAction()
    {
        if ($this->getRequest()->isGet()) {
            $data = $this->getRequest()->getQuery();
            $tradeId = false;
            foreach ($this->tradeIndex as $index) {
                if ($data[$index]) {
                    $tradeId = $data[$index];
                    break;
                }
            }
            if ($tradeId) {
                $log = new Payment;
                $log->load($tradeId, 'trade_id');
                $method = new $log['method'];
                $response = $method->syncNotice($data);
                if ($response !== false) {
                    return strpos($response, '://') ? $this->redirect($response) : $response;
                }
            }
        }
        return $this->notFoundAction();
    }

}
