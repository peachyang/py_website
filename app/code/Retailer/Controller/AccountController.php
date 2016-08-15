<?php

namespace Seahinet\Retailer\Controller;

use Seahinet\Lib\Session\Segment;
use Seahinet\Retailer\Model\Retailer;

class AccountController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout('retailer_account');
    }

    public function applyAction()
    {
        return $this->getLayout('retailer_apply');
    }

    public function applyPostAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['name', 'address', 'account', 'photo', 'credentials', 'status']);
            if ($result['error'] === 0) {
                $model = new Retailer($data);
                $model->setData([
                    'id' => null,
                    'customer_id' => (new Segment('customer'))->get('customer')->getId(),
                    'store_id' => null,
                    'status' => 0
                ]);
                try {
                    $model->save();
                    $result['data'] = $model->getArrayCopy();
                    $result['message'][] = ['message' => $this->translate('An item has been saved successfully.'), 'level' => 'success'];
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $result['message'][] = ['message' => $this->translate('An error detected while saving. Please check the log report or try again.'), 'level' => 'danger'];
                    $result['error'] = 1;
                }
            }
        }
        return $this->response(isset($result) ? $result : ['error' => 0, 'message' => []], 'retail/account/processing/', 'customer');
    }

    public function processingAction()
    {
        return $this->getLayout('retailer_processing');
    }

}
