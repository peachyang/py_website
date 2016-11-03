<?php

namespace Seahinet\Retailer\Controller;

use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\{
    Order,
    Order\Phase,
    Rma
};

class RefundController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout('retailer_refund');
    }

    public function viewAction()
    {
        if ($id = $this->getRequest()->getQuery('id')) {
            $rma = new Rma;
            $rma->load($id);
            if ($rma->getId()) {
                $root = $this->getLayout('retailer_refund_view');
                $root->getChild('main', true)->setVariable('model', $rma);
                return $root;
            }
        }
        return $this->redirectReferer('retailer/refund/');
    }

    public function addCommentAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['rma_id', 'comment']);
            if ($result['error'] === 0) {
                try {
                    $images = [];
                    $path = BP . 'pub/upload/refund/';
                    if (!is_dir($path)) {
                        mkdir($path, 0755, true);
                    }
                    $count = 0;
                    $files = $this->getRequest()->getUploadedFile();
                    if (!empty($files['voucher'])) {
                        foreach ($files['voucher'] as $file) {
                            if ($file->getError() === UPLOAD_ERR_OK && $count++ < 5) {
                                $newName = $file->getClientFilename();
                                while (file_exists($path . $newName)) {
                                    $newName = preg_replace('/(\.[^\.]+$)/', random_int(0, 9) . '$1', $newName);
                                    if (strlen($newName) >= 120) {
                                        throw new Exception('The file is existed.');
                                    }
                                }
                                $file->moveTo($path . $newName);
                                $images[] = $newName;
                            }
                        }
                    }
                    $refund = new Rma;
                    $refund->setId($data['rma_id'])->addComment([
                        'is_customer' => 1,
                        'comment' => $data['comment'],
                        'image' => json_encode($images)
                    ]);
                    $result['message'][] = ['message' => $this->translate('An item has been saved successfully.'), 'level' => 'success'];
                } catch (Exception $e) {
                    $this->rollback();
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('An error detected. Please contact us or try again later.'), 'level' => 'danger'];
                }
            }
        }
        return $this->response($result ?? ['error' => 0, 'message' => []], 'retailer/refund/', 'customer');
    }

    public function deliverAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['rma_id', 'carrier', 'tracking_number']);
            if ($result['error'] === 0) {
                try {
                    $refund = new Rma;
                    $refund->load($data['rma_id']);
                    $segment = new Segment('customer');
                    if ($segment->get('hasLoggedIn') && $segment->get('customer')->getId() != $refund['customer_id'] ||
                            !$segment->get('hasLoggedIn') && $refund['customer_id'] ||
                            !$refund['service'] && $refund['status'] != 1) {
                        $result['error'] = 1;
                        $result['message'][] = ['message' => $this->translate('Invalid application ID'), 'level' => 'danger'];
                    } else {
                        $refund->setData('status', 2)->save()->addComment([
                            'is_customer' => 1,
                            'comment' => $this->translate('Carrier: %s<br />Tracking Number: %s', [$data['carrier'], $data['tracking_number']]),
                        ]);
                        $result['message'][] = ['message' => $this->translate('An item has been saved successfully.'), 'level' => 'success'];
                    }
                } catch (Exception $e) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('An error detected. Please contact us or try again later.'), 'level' => 'danger'];
                }
            }
        }
        return $this->response($result ?? ['error' => 0, 'message' => []], 'retailer/refund/', 'customer');
    }

    public function confirmAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['rma_id']);
            if ($result['error'] === 0) {
                try {
                    $refund = new Rma;
                    $refund->load($data['rma_id']);
                    $segment = new Segment('customer');
                    if ($segment->get('hasLoggedIn') && $segment->get('customer')->getId() != $refund['customer_id'] ||
                            !$segment->get('hasLoggedIn') && $refund['customer_id'] ||
                            $refund['service'] < 2 && $refund['status'] != 4) {
                        $result['error'] = 1;
                        $result['message'][] = ['message' => $this->translate('Invalid application ID'), 'level' => 'danger'];
                    } else {
                        $this->beginTransaction();
                        $refund->setData('status', 5)->save();
                        $refund->getOrder()->rollbackStatus();
                        $result['message'][] = ['message' => $this->translate('The application has been complete.'), 'level' => 'success'];
                        $this->commit();
                    }
                } catch (Exception $e) {
                    $this->rollback();
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('An error detected. Please contact us or try again later.'), 'level' => 'danger'];
                }
            }
        }
        return $this->response($result ?? ['error' => 0, 'message' => []], 'retailer/refund/', 'customer');
    }

}
