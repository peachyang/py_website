<?php

namespace Seahinet\Checkout\ViewModel\Order;

use Seahinet\Customer\Model\Address as Model;
use Seahinet\Customer\Model\Collection\Address as Collection;
use Seahinet\I18n\Model\Locate;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\ViewModel\Template;
use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\Cart;

class Address extends Template
{

    public function getAddressAttribute()
    {
        $collection = new Attribute;
        $collection->withLabel()
                ->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [], 'left')
                ->where(['eav_entity_type.code' => Collection::ENTITY_TYPE])
                ->order('id');
        return $collection;
    }

    public function getAddress()
    {
        $segment = new Segment('customer');
        if ($segment->get('hasLoggedIn')) {
            $address = new Collection;
            $address->where(['customer_id' => $segment->get('customer')['id']]);
            if ($address->count()) {
                return $address;
            }
        } else if (Cart::instance()['shipping_address_id']) {
            $address = new Model;
            $address->load(Cart::instance()['shipping_address_id']);
            return [$address];
        }
        return null;
    }

    public function getInputBox($key, $item)
    {
        if (empty($item['type'])) {
            return '';
        }
        $class = empty($item['view_model']) ? '\\Seahinet\\Lib\\ViewModel\\Template' : $item['view_model'];
        $box = new $class;
        $box->setVariables([
            'key' => $key,
            'item' => $item,
            'parent' => $this
        ]);
        $box->setTemplate('page/renderer/' . $item['type']);
        return $box;
    }

    public function getCountries()
    {
        $countries = (new Locate)->load('country');
        $result = [];
        $language = Bootstrap::getLanguage()['code'];
        foreach($countries as $country){
            $result[$country['id']] = $country['name'][$language];
        }
        return $result;
    }

}
