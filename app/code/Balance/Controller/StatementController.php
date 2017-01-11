<?php

namespace Seahinet\Balance\Controller;

use Exception;
use Seahinet\Customer\Controller\AuthActionController;
use Seahinet\Customer\Model\Balance as Model;
use Seahinet\Lib\Session\Segment;

class StatementController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout('balance_statement');
    }

    public function rechargeAction()
    {
        return $this->getLayout('balance_statement_recharge');
    }

    public function rechargePaymentAction()
    {
        return $this->getLayout('balance_recharge_payment');
    }

    public function cancelAction()
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

    public function saveAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $segment = new Segment('customer');
            $customer = $segment->get('customer');
            if ($result['error'] === 0) {
                try {
                    $model = new Model;
                    $model->load($customer['id']);
                    $model->setData($data);
                    $this->getContainer()->get('eventDispatcher')->trigger('frontend.customer.balance.save.before', ['model' => $model, 'data' => $data]);
                    $model->save();
                    $this->getContainer()->get('eventDispatcher')->trigger('frontend.customer.balance.save.after', ['model' => $model, 'data' => $data]);
                    $segment->set('customer', clone $model);
                    $result['message'][] = ['message' => $this->translate('An item has been saved successfully.'), 'level' => 'success'];
                } catch (Exception $e) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('An error detected while saving.'), 'level' => 'success'];
                }
            }
        }
        return $this->response($result ?? ['error' => 0, 'message' => []], 'balance/statement/', 'balance');
    }

}
