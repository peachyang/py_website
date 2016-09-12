<?php

namespace Seahinet\Retailer\Controller;

use Exception;
use Seahinet\Retailer\Model\Application;
use Seahinet\Retailer\Model\Retailer as Rmodel;
use Seahinet\Customer\Model\Customer as Cmodel;
use Seahinet\Lib\Session\Segment;

class AccountController extends AuthActionController
{

    public function indexAction()
    {
        $segment = new Segment('customer');
        if ($customerId = $segment->get('customer')->getId()) {
            $customer = new Cmodel;
            $customer->load($customerId);
            $root = $this->getLayout('retailer_account_dashboard');
            $root->getChild('main', true)->setVariable('customer', $customer);
            return $root;
        }
        return $root;
    }

    public function applyAction()
    {
        return $this->getLayout('retailer_apply');
    }

    public function reapplyAction()
    {
        $model = new Application;
        $model->load((new Segment('customer'))->get('customer')->getId());
        $root = $this->getLayout('retailer_apply');
        $root->getChild('main', true)->setVariable('data', $model->toArray());
        return $root;
    }

    public function applyPostAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['phone', 'brand_type', 'product_type']);
            $files = $this->getRequest()->getUploadedFile();
            if (count($files) < 2) {
                $result['message'][] = ['message' => $this->translate('The lisence images are required and cannot be empty.', [], 'retailer'), 'level' => 'danger'];
                $result['error'] = 1;
            } else if ($files['id1']->getSize() > 1048576 || $files['id2']->getSize() > 1048576) {
                $result['message'][] = ['message' => $this->translate('You probably tried to upload a file that is too large.', [], 'retailer'), 'level' => 'danger'];
                $result['error'] = 1;
            }
            if ($result['error'] === 0) {
                $model = new Application($data);
                try {
                    $model->setData([
                        'id' => null,
                        'customer_id' => (new Segment('customer'))->get('customer')->getId(),
                        'lisence_1' => $files['id1']->getStream()->getContents(),
                        'lisence_2' => $files['id2']->getStream()->getContents(),
                        'status' => 0
                    ]);
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
