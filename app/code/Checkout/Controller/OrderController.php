<?php

namespace Seahinet\Checkout\Controller;

use Exception;
use Seahinet\Customer\Model\Address;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\Cart;
use Seahinet\Sales\Model\Order;

class OrderController extends ActionController
{

    use \Seahinet\Lib\Traits\DB;

    public function dispatch($request = null, $routeMatch = null)
    {
        $session = new Segment('customer');
        if (!$session->get('hasLoggedIn') && !$this->getContainer()->get('config')['checkout/general/allow_guest']) {
            return $this->redirect('customer/account/login/?success_url=' . str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($this->getBaseUrl('checkout/order/'))));
        } else if (Cart::instance()->offsetGet('base_total') < ($min = (float) $this->getContainer()->get('config')['checkout/sales/min_amount'])) {
            $currency = Cart::instance()->getCurrency();
            $this->addMessage($this->translate('The allowed minimal amount is %s, current is %s.', [$currency->convert($min, true), $currency->format(Cart::instance()->offsetGet('total'))]));
            return $this->redirect('checkout/cart/');
        }
        return parent::dispatch($request, $routeMatch);
    }

    public function indexAction()
    {
        if (count(Cart::instance()->getItems())) {
            return $this->getLayout('checkout_order');
        }
        return $this->redirectReferer('checkout/cart/');
    }

    public function shippingAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->getLayout('checkout_order_shipping');
        }
        return $this->notFoundAction();
    }

    public function paymentAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->getLayout('checkout_order_payment');
        }
        return $this->notFoundAction();
    }

    public function reviewAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->getLayout('checkout_order_review');
        }
        return $this->notFoundAction();
    }

    public function couponAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $root = $this->getLayout('checkout_order_coupon');
            $root->getChild('coupon', true)->setVariable('store', $this->getRequest()->getQuery('store'));
            return $root;
        }
        return $this->notFoundAction();
    }

    public function placeAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if (!isset($data['csrf']) || !$this->validateCsrfKey($data['csrf'])) {
                $result['message'][] = ['message' => $this->translate('The form submitted did not originate from the expected site.'), 'level' => 'danger'];
                $result['error'] = 1;
            } else {
                try {
                    $this->beginTransaction();
                    $billingAddress = $this->validBillingAddress($data);
                    $paymentMethod = $this->validPayment($data);
                    $cart = Cart::instance();
                    if ($cart->isVirtual()) {
                        $cart->setData([
                            'payment_method' => $data['payment_method'],
                            'customer_note' => isset($data['comment']) ? json_encode($data['comment']) : '{}'
                        ]);
                        if ($billingAddress) {
                            $cart->setData([
                                'billing_address_id' => $data['billing_address_id'],
                                'billing_address' => $billingAddress->display(false)
                            ]);
                        }
                    } else {
                        $shippingAddress = $this->validShippingAddress($data);
                        $this->validShipping($data);
                        $cart->setData([
                            'shipping_address_id' => $data['shipping_address_id'],
                            'shipping_address' => isset($shippingAddress) ? $shippingAddress->display(false) : '',
                            'payment_method' => $data['payment_method'],
                            'shipping_method' => json_encode($data['shipping_method']),
                            'customer_note' => isset($data['comment']) ? json_encode($data['comment']) : '{}'
                        ])->setData($billingAddress ? [
                                    'billing_address_id' => $data['billing_address_id'],
                                    'billing_address' => $billingAddress->display(false)
                                        ] : [
                                    'billing_address_id' => $data['shipping_address_id'],
                                    'billing_address' => $shippingAddress->display(false)
                        ]);
                    }
                    $items = $cart->getItems(true);
                    $items->columns(['warehouse_id', 'store_id'])->group('warehouse_id')->group('store_id');
                    $orders = [];
                    if (isset($data['payment_data'])) {
                        $paymentMethod->saveData($cart, $data['payment_data']);
                    }
                    $result['redirect'] = $paymentMethod->preparePayment();
                    $items->walk(function($item) use (&$orders, $paymentMethod) {
                        $orders[] = (new Order)->place($item['warehouse_id'], $item['store_id'], $paymentMethod->getNewOrderStatus());
                    });
                    $cart->abandon();
                    $this->commit();
                    $segment = new Segment('checkout');
                    $segment->set('hasNewOrder', 1);
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate($e->getMessage()), 'level' => 'danger'];
                    $this->rollback();
                }
            }
        }
        return $this->response($result, 'checkout/order/', 'checkout');
    }

    protected function validShippingAddress($data)
    {
        if (!isset($data['shipping_address_id'])) {
            throw new Exception('Please select shipping address');
        }
        $address = new Address;
        $address->load($data['shipping_address_id']);
        if ($address->offsetGet('customer_id')) {
            $segment = new Segment('customer');
            if (!$segment->get('hasLoggedIn') || $segment->get('customer')->getId() != $address->offsetGet('customer_id')) {
                throw new Exception('Invalid address ID');
            }
        }
        return $address;
    }

    protected function validBillingAddress($data)
    {
        if (!isset($data['billing_address_id'])) {
            return null;
        }
        $address = new Address;
        $address->load($data['billing_address_id']);
        if ($address->offsetGet('customer_id')) {
            $segment = new Segment('customer');
            if (!$segment->get('hasLoggedIn') || $segment->get('customer')->getId() != $address->offsetGet('customer_id')) {
                throw new Exception('Invalid address ID');
            }
        }
        return $address;
    }

    public function saveAddressAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $attribute = new Attribute;
            $attribute->withSet()
                    ->columns(['code'])
                    ->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [])
                    ->where(['eav_entity_type.code' => Address::ENTITY_TYPE, 'is_required' => 1]);
            $required = [];
            $setId = $attribute[0]['attribute_set_id'];
            $attribute->walk(function($item) use (&$required) {
                $required[] = $item['code'];
            });
            $result = $this->validateForm($data, $required);
            if ($result['error'] === 0) {
                $address = new Address;
                unset($data['customer_id']);
                unset($data['store_id']);
                unset($data['attribute_set_id']);
                try {
                    $segment = new Segment('customer');
                    $address->setData($data + [
                        'attribute_set_id' => $setId,
                        'store_id' => Bootstrap::getStore()->getId(),
                        'customer_id' => $segment->get('hasLoggedIn') ? $segment->get('customer')->getId() : null
                    ])->save();
                    if (!$segment->get('hasLoggedIn')) {
                        $ids = $segment->get('address') ?: [];
                        $ids[] = $address->getId();
                        $segment->set('address', $ids);
                    }
                    $result['data'] = ['id' => $address->getId(), 'content' => $address->display(), 'json' => json_encode($address->toArray())];
                    Cart::instance()->setData(
                            $data['is_billing'] ? [
                                'billing_address_id' => $result['data']['id'],
                                'billing_address' => $result['data']['content']
                                    ] : [
                                'shipping_address_id' => $result['data']['id'],
                                'shipping_address' => $result['data']['content'],
                                'billing_address_id' => $result['data']['id'],
                                'billing_address' => $result['data']['content']
                            ])->save();
                } catch (Exception $e) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('An error detected while saving. Please contact us or try again later.'), 'level' => 'danger'];
                }
            }
        }
        return $this->response($result, 'checkout/order/', 'checkout');
    }

    public function deleteAddressAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isDelete()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['id']);
            if ($result['error'] === 0) {
                $address = new Address;
                try {
                    $address->load($data['id']);
                    if ($address->offsetGet('customer_id')) {
                        $segment = new Segment('customer');
                        if (!$segment->get('hasLoggedIn') || $segment->get('customer')->getId() != $address->offsetGet('customer_id')) {
                            throw new Exception('Invalid address ID');
                        }
                    } else if ($data['id'] != Cart::instance()['shipping_address_id'] && $data['id'] != Cart::instance()['billing_address_id']) {
                        throw new Exception('Invalid address ID');
                    }
                    $address->remove();
                    $result['removeLine'] = 1;
                } catch (Exception $e) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate($e->getMessage()), 'level' => 'danger'];
                }
            }
        }
        return $this->response($result, 'checkout/order/', 'checkout');
    }

    public function selectAddressAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if (!isset($data['csrf']) || !$this->validateCsrfKey($data['csrf'])) {
                $result['message'][] = ['message' => $this->translate('The form submitted did not originate from the expected site.'), 'level' => 'danger'];
                $result['error'] = 1;
            } else {
                try {
                    $billingAddress = $this->validBillingAddress($data);
                    $cart = Cart::instance();
                    if ($cart->isVirtual()) {
                        if ($billingAddress) {
                            $cart->setData([
                                'billing_address_id' => $data['billing_address_id'],
                                'billing_address' => $billingAddress->display(false)
                            ])->collateTotals();
                        }
                    } else {
                        $shippingAddress = $this->validShippingAddress($data);
                        $cart->setData([
                            'shipping_address_id' => $data['shipping_address_id'],
                            'shipping_address' => $shippingAddress->display(false)
                        ])->setData($billingAddress ? [
                                    'billing_address_id' => $data['billing_address_id'],
                                    'billing_address' => $billingAddress->display(false)
                                        ] : [
                                    'billing_address_id' => $data['shipping_address_id'],
                                    'billing_address' => $shippingAddress->display(false)
                                ])->collateTotals();
                    }
                } catch (Exception $e) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate($e->getMessage()), 'level' => 'danger'];
                }
            }
        }
        return $this->response($result, 'checkout/order/', 'checkout');
    }

    public function validPayment($data)
    {
        if (!isset($data['payment_method'])) {
            throw new Exception('Please select payment method');
        }
        $className = $this->getContainer()->get('config')['payment/' . $data['payment_method'] . '/model'];
        $method = new $className;
        $result = $method->available($data);
        if ($result !== true) {
            throw new Exception(is_string($result) ? $result : 'Invalid payment method');
        }
        return $method;
    }

    public function selectPaymentAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if (!isset($data['csrf']) || !$this->validateCsrfKey($data['csrf'])) {
                $result['message'][] = ['message' => $this->translate('The form submitted did not originate from the expected site.'), 'level' => 'danger'];
                $result['error'] = 1;
            } else {
                try {
                    $this->validPayment($data);
                    $cart = Cart::instance();
                    $cart->setData([
                        'payment_method' => $data['payment_method']
                    ])->collateTotals();
                } catch (Exception $e) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate($e->getMessage()), 'level' => 'danger'];
                }
            }
        }
        return $this->response($result, 'checkout/order/', 'checkout');
    }

    public function validShipping($data)
    {
        if (!isset($data['shipping_method'])) {
            throw new Exception('Please select shipping method');
        }
        $cart = Cart::instance();
        $result = [];
        foreach ($cart->getItems() as $item) {
            if (!isset($result[$item['store_id']])) {
                if (!isset($data['shipping_method'][$item['store_id']])) {
                    throw new Exception('Invalid shipping method');
                }
                $className = $this->getContainer()->get('config')['shipping/' . $data['shipping_method'][$item['store_id']] . '/model'];
                $result[$item['store_id']] = new $className;
                if (!$result[$item['store_id']]->available($data)) {
                    throw new Exception('Invalid shipping method');
                }
            }
        }
        return $result;
    }

    public function selectShippingAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if (!isset($data['csrf']) || !$this->validateCsrfKey($data['csrf'])) {
                $result['message'][] = ['message' => $this->translate('The form submitted did not originate from the expected site.'), 'level' => 'danger'];
                $result['error'] = 1;
            } else {
                try {
                    $cart = Cart::instance();
                    if (!$cart->isVirtual()) {
                        $this->validShipping($data);
                        $cart->setData([
                            'shipping_method' => json_encode($data['shipping_method'])
                        ])->collateTotals();
                    }
                } catch (Exception $e) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate($e->getMessage()), 'level' => 'danger'];
                }
            }
        }
        return $this->response($result, 'checkout/order/', 'checkout');
    }

    public function selectCouponAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if (!isset($data['csrf']) || !$this->validateCsrfKey($data['csrf'])) {
                $result['message'][] = ['message' => $this->translate('The form submitted did not originate from the expected site.'), 'level' => 'danger'];
                $result['error'] = 1;
            } else {
                try {
                    $cart = Cart::instance();
                    $cart->setData([
                        'coupon' => json_encode($data['coupon'])
                    ])->collateTotals();
                } catch (Exception $e) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate($e->getMessage()), 'level' => 'danger'];
                }
            }
        }
        return $this->response($result, 'checkout/order/', 'checkout');
    }

}
