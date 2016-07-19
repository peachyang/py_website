<?php

namespace Seahinet\Checkout\Controller;

use Exception;
use Seahinet\Catalog\Exception\OutOfStock;
use Seahinet\Catalog\Model\Product;
use Seahinet\Lib\Controller\ActionController;
use Seahinet\Sales\Model\Cart;

class CartController extends ActionController
{

    public function addAction()
    {
        $data = $this->getRequest()->isGet() ? $this->getRequest()->getQuery() : $this->getRequest()->getPost();
        $result = $this->validateForm($data, ['product_id', 'qty', 'warehouse_id']);
        if ($result['error'] === 0) {
            $cart = Cart::instance();
            try {
                $cart->addItem($data['product_id'], $data['qty'], $data['warehouse_id'], isset($data['options']) ? $data['options'] : [], isset($data['sku']) ? $data['sku'] : '' );
                $result['message'][] = ['message' => $this->translate('%s has been added to your shopping cart.', [(new Product)->load($data['product_id'])['name']]), 'level' => 'success'];
            } catch (OutOfStock $e) {
                $result['error'] = 1;
                $result['message'][] = ['message' => $this->translate('The requested quantity for "%s" is not available.', [(new Product)->load($data['product_id'])['name']]), 'level' => 'danger'];
            } catch (Exception $e) {
                $result['error'] = 1;
                $result['message'][] = ['message' => $this->translate('An error detected. Please contact us or try again later.'), 'level' => 'danger'];
                $this->getContainer()->get('log')->logException($e);
            }
        }
        return $this->response($result, 'checkout/cart/', 'checkout');
    }

    public function removeAction()
    {
        $data = $this->getRequest()->isGet() ? $this->getRequest()->getQuery() : $this->getRequest()->getPost();
        $result = $this->validateForm($data);
        if ($result['error'] === 0) {
            $cart = Cart::instance();
            try {
                if (isset($data['id'])) {
                    $item = $cart->getItem($data['id']);
                    if ($item) {
                        $cart->removeItem($data['id']);
                        $result['message'][] = ['message' => $this->translate('%s has been removed from your shopping cart.', [$item['product_name']]), 'level' => 'success'];
                    } else {
                        return $this->redirectReferer('checkout/cart/');
                    }
                } else {
                    $cart->removeAllItems();
                    $result['message'][] = ['message' => $this->translate('All items have been removed from your shopping cart.'), 'level' => 'success'];
                }
            } catch (Exception $e) {
                $result['error'] = 1;
                $result['message'][] = ['message' => $this->translate('An error detected. Please contact us or try again later.'), 'level' => 'danger'];
                $this->getContainer()->get('log')->logException($e);
            }
        }
        return $this->response($result, 'checkout/cart/', 'checkout');
    }

    public function updateAction()
    {
        $data = $this->getRequest()->isGet() ? $this->getRequest()->getQuery() : $this->getRequest()->getPost();
        $result = $this->validateForm($data, ['qty']);
        if ($result['error'] === 0) {
            $cart = Cart::instance();
            try {
                foreach ($data['qty'] as $id => $qty) {
                    try {
                        $cart->changeQty($id, $qty);
                    } catch (OutOfStock $e) {
                        $result['error'] = 1;
                        $result['message'][] = ['message' => $this->translate('The requested quantity for "%s" is not available.', [$cart->getItem($id)['name']]), 'level' => 'danger'];
                    }
                }
            } catch (Exception $e) {
                $result['error'] = 1;
                $result['message'][] = ['message' => $this->translate('An error detected. Please contact us or try again later.'), 'level' => 'danger'];
                $this->getContainer()->get('log')->logException($e);
            }
        }
        return $this->response($result, 'checkout/cart/', 'checkout');
    }

    public function indexAction()
    {
        return $this->getLayout('checkout_cart');
    }

    public function miniAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->getLayout('checkout_cart_mini');
        }
        return $this->notFoundAction();
    }

}
