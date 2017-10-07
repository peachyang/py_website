<?php

namespace Seahinet\Distribution\Listeners;

use Seahinet\Catalog\Model\Product;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Lib\ViewModel\Root;
use Seahinet\Sales\Model\Cart;

class Refer implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container;

    public function afterDispatchProduct($event)
    {
        $result = $event['result'];
        if ($result instanceof Root && ($product = $result->getChild('product', true)) && ($referer = $this->getContainer()->get('request')->getQuery('referer'))) {
            $cookie = json_decode($this->getContainer()->get('request')->getCookie('referer', '[]'), true);
            $cookie[$this->getContainer()->get('config')['distribution/general/refer'] ? $product->getProduct()->getId() : $product->getProduct()->offsetGet('store_id')] = $referer;
            $this->getContainer()->get('response')->withCookie('referer', json_encode($cookie));
        }
    }

    public function afterDispatchStore($event)
    {
        if ($event['result'] instanceof Root && ($this->getContainer()->get('config')['distribution/general/refer']) && ($referer = $this->getContainer()->get('request')->getQuery('referer'))) {
            $cookie = json_decode($this->getContainer()->get('request')->getCookie('referer', '[]'), true);
            $cookie[Bootstrap::getStore()->getId()] = $referer;
            $this->getContainer()->get('response')->withCookie('referer', json_encode($cookie));
        }
    }

    private function setAdditional($id, $key)
    {
        $cookie = json_decode($this->getContainer()->get('request')->getCookie('referer', '[]'), true);
        if (isset($cookie[$key])) {
            $cart = Cart::instance();
            $additional = json_decode($cart->offsetGet('additional') ?: '[]', true);
            if (!isset($additional['referer'])) {
                $additional['referer'] = [];
            }
            $additional['referer'][$id] = $cookie[$key];
            $cart->setData('additional', json_encode($additional))->save();
            unset($cookie[$key]);
            $this->getContainer()->get('response')->withCookie('referer', json_encode($cookie));
        }
    }

    public function afterAdd2Cart($event)
    {
        if ($this->getContainer()->get('config')['distribution/general/refer']) {
            $product = new Product;
            $product->load($event['product_id']);
            $this->setAdditional($event['product_id'], $product->offsetGet('store_id'));
        } else {
            $this->setAdditional($event['product_id'], $event['product_id']);
        }
    }

}
