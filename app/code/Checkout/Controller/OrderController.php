<?php

namespace Seahinet\Checkout\Controller;

use Exception;
use Seahinet\Customer\Model\Address;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\Cart;

class OrderController extends ActionController
{

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

    public function saveAddressAction()
    {
        $result = ['error' => 1, 'message' => []];
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
                    $result['data'] = ['id' => $address->getId(), 'content' => $address->display()];
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
                    $result['message'] = ['message' => $this->translate('An error detected while saving. Please contact us or try again later.'), 'level' => 'danger'];
                }
            }
        }
        return $this->response($result, 'checkout/order/', 'checkout');
    }

    public function deleteAddressAction()
    {
        $result = ['error' => 1, 'message' => []];
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
                    $result['message'] = ['message' => $this->translate('An error detected while deleting. Please contact us or try again later.'), 'level' => 'danger'];
                }
            }
        }
        return $this->response($result, 'checkout/order/', 'checkout');
    }

}
