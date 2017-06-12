<?php

namespace Seahinet\Payment\Controller;

use Seahinet\Lib\Controller\ActionController;
use Seahinet\Log\Model\Payment;
use SimpleXMLElement;

class NotifyController extends ActionController
{

    protected $tradeIndex = [
        'out_trade_no'
    ];

    protected function xmlToArray(SimpleXMLElement $xml)
    {
        $result = (array) $xml;
        foreach ($xml as &$child) {
            if ($child instanceof SimpleXMLElement){
                $child = $this->xmlToArray($child);
            }
        }
        return $result;
    }

    public function indexAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if (is_object($data)) {
                $data = $this->xmlToArray($data);
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
