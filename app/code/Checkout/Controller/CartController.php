<?php

namespace Seahinet\Checkout\Controller;

use Exception;
use Seahinet\Catalog\Exception\OutOfStock;
use Seahinet\Catalog\Model\Product;
use Seahinet\Customer\Model\Wishlist;
use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\Cart;
use Seahinet\Sales\Model\Cart\Item;

class CartController extends ActionController
{

    public function addAction()
    {
        $data = $this->getRequest()->isGet() ? $this->getRequest()->getQuery() : $this->getRequest()->getPost();
        $result = $this->validateForm($data, ['product_id', 'qty', 'warehouse_id']);
        if ($result['error'] === 0) {
            try {
                $product = new Product;
                $options = $product->load($data['product_id'])->getOptions(['is_required' => 1]);
                foreach ($options as $option) {
                    if (!isset($data['options'][$option->getId()])) {
                        $result['error'] = 1;
                        $result['message'][] = ['message' => sprintf($this->translate('The %%s field is required and cannot be empty.'), $option->offsetGet('title')), 'level' => 'danger'];
                    }
                }
                if ($result['error'] === 1) {
                    return $this->response($result, $product->getUrl(), 'checkout');
                }
                Cart::instance()->addItem($data['product_id'], $data['qty'], $data['warehouse_id'], isset($data['options']) ? $data['options'] : [], isset($data['sku']) ? $data['sku'] : '' );
                $result['message'][] = ['message' => $this->translate('"%s" has been added to your shopping cart.', [(new Product)->load($data['product_id'])['name']]), 'level' => 'success'];
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
                    if (is_array($data['id'])) {
                        $cart->removeItems($data['id']);
                        $result['message'][] = ['message' => $this->translate('%d item(s) has been removed from your shopping cart.', [count($data['id'])]), 'level' => 'success'];
                    } else {
                        $item = $cart->getItem($data['id']);
                        if ($item) {
                            $cart->removeItem($data['id']);
                            $result['message'][] = ['message' => $this->translate('"%s" has been removed from your shopping cart.', [$item['product_name']]), 'level' => 'success'];
                        } else {
                            return $this->redirectReferer('checkout/cart/');
                        }
                    }
                } else {
                    $cart->removeAllItems();
                    $result['message'][] = ['message' => $this->translate('All items have been removed from your shopping cart.'), 'level' => 'success'];
                }
                $result['reload'] = 1;
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
        $result = $this->validateForm($data, ['qty', 'item']);
        if ($result['error'] === 0) {
            $cart = Cart::instance();
            try {
                foreach ($data['qty'] as $id => $qty) {
                    try {
                        if (in_array($id, $data['item'])) {
                            $cart->changeQty($id, $qty, false);
                        } else {
                            $cart->changeItemStatus($id, false, false);
                        }
                    } catch (OutOfStock $e) {
                        $result['error'] = 1;
                        $result['message'][] = ['message' => $this->translate('The requested quantity for "%s" is not available.', [$cart->getItem($id)['name']]), 'level' => 'danger'];
                    }
                }
                $cart->collateTotals();
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

    public function moveToWishlistAction()
    {
        $segment = new Segment('customer');
        if (!$segment->get('hasLoggedIn')) {
            $segment->set('afterLogin', 'checkout/cart/');
            return $this->redirect('customer/account/login/');
        }
        $data = $this->getRequest()->isGet() ? $this->getRequest()->getQuery() : $this->getRequest()->getPost();
        $result = $this->validateForm($data, ['id']);
        if ($result['error'] === 0) {
            $wishlist = new Wishlist;
            $wishlist->load($segment->get('customer')['id'], 'customer_id');
            if (!$wishlist->getId()) {
                $wishlist->setData('customer_id', $segment->get('customer')['id'])->save();
            }
            try {
                foreach ((array) $data['id'] as $id) {
                    $item = Cart::instance()->getItem($id);
                    $wishlist->addItem([
                        'product_id' => $item['product_id'],
                        'product' => $item['product'],
                        'qty' => $item['qty'],
                        'options' => $item['options']
                    ]);
                    $result['message'][] = ['message' => $this->translate('"%s" has been moved to wishlist.', [$item['product']['name']]), 'level' => 'success'];
                    Cart::instance()->removeItem($item);
                }
            } catch (Exception $e) {
                $result['error'] = 1;
                $result['message'][] = ['message' => $this->translate('An error detected. Please contact us or try again later.'), 'level' => 'danger'];
                $this->getContainer()->get('log')->logException($e);
            }
        }
        return $this->response($result, 'checkout/cart/', 'checkout');
    }

}