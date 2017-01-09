<?php

namespace Seahinet\Balance\Controller;

use Seahinet\Customer\Controller\AuthActionController;
use Seahinet\Customer\Model\Balance;
use Seahinet\Lib\Session\Segment;

class StatementController extends AuthActionController
{

    public function IndexAction()
    {
        return $this->getLayout('balance_statement');
    }

    public function RechargeAction()
    {
        return $this->getLayout('balance_statement_recharge');
    }

    public function rechargePaymentActon()
    {
        return $this->getLayout('balance_recharge_payment');
    }

    public function CancelAction()
    {
        if ($this->getRequest()->isDelete()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['id']);
            $item = Balance;
            if ($result['error'] === 0) {
                try {
                    $item->setId($data['id'])->remove();
                    $result['removeLine'] = 1;
                    $result['message'][] = ['message' => $this->translate('The product has been removed from wishlist successfully.'), 'level' => 'success'];
                } catch (Exception $e) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('An error detected while deleting. Please contact us or try again later.'), 'level' => 'success'];
                }
            }
        }
        return $this->response($result ?? ['error' => 0, 'message' => []], 'balance/statement/', 'customer');
    }

    public function SaveAction()
    {
        return $this->rechargePaymentActon();
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $segment = new Segment('customer');
            $customer = $segment->get('customer');
            $result = $this->validateForm($data, ['integral']);
            if (!empty($data['integral'])) {
                return $this->rechargePaymentActon();
            } else {
                return 0;
            }
        }
        return $this->response($result ?? ['error' => 0, 'message' => []], 'balance/statement/', 'customer');
    }

}
