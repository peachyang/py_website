<?php

namespace Seahinet\LiveChat\ViewModel;

use Seahinet\Customer\Model\Customer;
use Seahinet\Lib\ViewModel\Template;
use Seahinet\LiveChat\Model\Collection\Session;
use Seahinet\Retailer\Model\Retailer;

class Wrapper extends Template
{

    protected $customerId;

    public function getCustomerId()
    {
        $this->customerId = $this->getSegment('customer')->get('customer')->getId();
        return $this->customerId;
    }

    public function getWsUrl()
    {
        $uri = $this->getRequest()->getUri();
        $config = $this->getConfig();
        return ($uri->getScheme() === 'https' ? 'wss:' : 'ws:') . $uri->withScheme('')
                        ->withFragment('')
                        ->withQuery('')
                        ->withPort($config['livechat/port'] ?: $uri->getPort())
                        ->withPath($config['livechat/path']);
    }

    public function getSessions()
    {
        $id = $this->getCustomerId();
        $collection = new Session;
        $collection->columns(['id'])
                ->where(['customer_id' => $id]);
        return $collection->load(true, true);
    }

    public function getTarget($id)
    {
        $collection = new Session;
        $collection->where(['id' => $id])
        ->where->notEqualTo('customer_id', $this->getCustomerId());
        $collection->load(true, true);
        if (count($collection)) {
            return $this->getName($collection[0]['customer_id']);
        }
        return null;
    }

    public function getInfo($id)
    {
        $retailer = new Retailer;
        $retailer->load($id, 'customer_id');
        if ($retailer->getId()) {
            return $retailer;
        } else {
            $customer = new Customer;
            $customer->load($id);
            return $customer;
        }
    }

    public function getName($id)
    {
        $info = $this->getInfo($id);
        if ($info instanceof Customer) {
            return $info['username'];
        } else {
            return $info->getStore()['name'];
        }
    }

}
