<?php

namespace Seahinet\Balance\Controller;

use Exception;
use Seahinet\Catalog\Model\Product;
use Seahinet\Customer\Controller\AuthActionController;
use Seahinet\Customer\Model\Balance as Model;
use Seahinet\Sales\Model\Cart;
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

    public function addAction()
    {
        $data = $this->getRequest()->isGet() ? $this->getRequest()->getQuery() : $this->getRequest()->getPost();
        $result = $this->validateForm($data, ['product_id', 'qty', 'warehouse_id']);
        if ($result['error'] === 0) {
            try {
                $product = new Product;
                $options = $product->load($data['product_id']);
                if ($result['error'] === 1) {
                    return $this->response($result, $product->getUrl(), 'checkout');
                }
                //$cart = new Cart;
                Cart::instance()->addItem($data['product_id'], $data['qty'], $data['warehouse_id'], $data['sku'] ?? '');
                //return $this->response($result, 'checkout/order/', 'checkout');
                $result['reload'] = 1;
                $result['message'][] = ['message' => $this->translate('"%s" has been added.', [(new Product)->load($data['product_id'])['name']]), 'level' => 'success'];
            } catch (Exception $e) {
                $result['error'] = 1;
                $result['message'][] = ['message' => $this->translate('Prohibit the purchase of goods sold.'), 'level' => 'danger'];
                $this->getContainer()->get('log')->logException($e);
            }
        }
        //return $this->response($result, 'checkout/order/', 'checkout');
        return $this->response($result ?? ['error' => 0, 'message' => []], 'checkout/order/', 'checkout');
    }

}
