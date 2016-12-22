<?php

namespace Seahinet\Retailer\ViewModel\Sales;

use Seahinet\Customer\Model\Address;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Retailer\ViewModel\AbstractViewModel;
use Seahinet\Sales\Model\Collection\{
    Order as Collection,
    Shipment\Track
};
use Seahinet\Sales\Source\Order\Status;
use Zend\Db\Sql\Expression;

class Order extends AbstractViewModel
{

    use \Seahinet\Lib\Traits\Filter;

    public function getCollection()
    {
        $collection = new Collection;
        $select = $collection->where(['store_id' => $this->getRetailer()['store_id']])
                ->order('created_at DESC');
        $languageId = Bootstrap::getLanguage()->getId();
        $attribute = new Attribute;
        $attribute->columns(['id', 'type'])
                ->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [], 'left')
                ->where([
                    'eav_attribute.code' => 'name',
                    'eav_entity_type.code' => Address::ENTITY_TYPE
        ]);
        $attribute->load(true, true);
        $select->join(['recipient_attr' => Address::ENTITY_TYPE . '_value_' . $attribute[0]['type']], new Expression('sales_order.shipping_address_id=recipient_attr.entity_id AND recipient_attr.language_id=' . $languageId . ' AND recipient_attr.attribute_id=' . $attribute[0]['id']), ['recipient' => 'value'], 'left');
        $attribute = new Attribute;
        $attribute->columns(['id', 'type'])
                ->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [], 'left')
                ->where([
                    'eav_attribute.code' => 'tel',
                    'eav_entity_type.code' => Address::ENTITY_TYPE
        ]);
        $attribute->load(true, true);
        $select->join(['tel_attr' => Address::ENTITY_TYPE . '_value_' . $attribute[0]['type']], new Expression('sales_order.shipping_address_id=tel_attr.entity_id AND tel_attr.language_id=' . $languageId . ' AND tel_attr.attribute_id=' . $attribute[0]['id']), ['tel' => 'value'], 'left');
        $data = $this->getQuery();
        if (!empty($data['created_at']) && count($data['created_at']) == 2 && !empty($data['created_at'][0]) && !empty($data['created_at'][1])) {
            $select->where->greaterThanOrEqualTo('created_at', $data['created_at'][0] . ' 00:00:00')
                    ->lessThanOrEqualTo('created_at', $data['created_at'][1] . ' 23:59:59');
        }
        if (!empty($data['track_number'])) {
            $track = new Track;
            $track->column(['order_id'])
                    ->where(['track_number' => $data['track_number']]);
            $collection->in('sales_order.id', $track);
        }
        if (!empty($data['recipient'])) {
            $data['recipient_attr.value'] = $data['recipient'];
        }
        if (!empty($data['tel'])) {
            $data['tel_attr.value'] = $data['tel'];
        }
        unset($data['created_at'], $data['recipient'], $data['tel'], $data['track_number'], $data['store_id']);
        $this->filter($collection, $data, ['order' => 1]);
        return $collection;
    }

    public function getFilters()
    {
        $data = $this->getQuery();
        return [
            'page' => [
                'type' => 'hidden',
                'value' => $data['page'] ?? 1
            ],
            'increment_id' => [
                'type' => 'text',
                'label' => 'Order ID',
                'value' => $data['increment_id'] ?? ''
            ],
            'status_id' => [
                'type' => 'select',
                'label' => 'Order Status',
                'options' => (new Status)->getSourceArray(),
                'value' => $data['status_id'] ?? ''
            ],
            'recipient' => [
                'type' => 'text',
                'label' => 'Recipient',
                'value' => $data['recipient'] ?? ''
            ],
            'tel' => [
                'type' => 'tel',
                'label' => 'Telephone',
                'value' => $data['tel'] ?? ''
            ],
            'track_number' => [
                'type' => 'text',
                'label' => 'Track Number',
                'value' => $data['track_number'] ?? ''
            ],
            'created_at[]' => [
                'type' => 'daterange',
                'label' => 'Placed at',
                'value' => $data['created_at'] ?? []
            ]
        ];
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
        $box->setTemplate('page/renderer/' . (in_array($item['type'], ['multiselect', 'checkbox']) ? 'select' : $item['type']), false);
        return $box;
    }

    public function renderItem($order)
    {
        $item = new static;
        $item->setTemplate('retailer/sales/order/item');
        $item->setVariable('order', $order);
        return $item;
    }

}
