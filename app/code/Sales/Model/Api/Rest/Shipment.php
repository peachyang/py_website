<?php

namespace Seahinet\Sales\Model\Api\Rest;

use Seahinet\Api\Model\Api\Rest\AbstractHandler;
use Seahinet\Customer\Model\Customer;
use Seahinet\Oauth\Model\Token;
use Seahinet\Sales\Model\Collection\{
    Order as OrderCollection,
    Shipment as ShipmentCollection,
    Shipment\Track as TrackCollection
};
use Seahinet\Sales\Model\Shipment\Track;

class Shipment extends AbstractHandler
{

    public function getShipment()
    {
        $data = $this->getRequest()->getQuery();
        $columns = $this->getAttributes('shipment');
        if (count($columns)) {
            if ($this->authOptions['validation'] > 0) {
                if (isset($data['openId']) && $data['openId'] === $this->authOptions['open_id']) {
                    $token = new Token;
                    $token->load($data['openId'], 'open_id');
                    unset($data['openId']);
                    $order = new OrderCollection;
                    $order->columns(['id'])
                            ->where(['customer_id' => $token['customer_id']]);
                } else {
                    return $this->getResponse()->withStatus(400);
                }
            }
            $collection = new ShipmentCollection;
            $collection->columns($columns);
            $this->filter($collection, $data);
            if ($order) {
                $collection->in('order_id', $order);
            }
            $result = [];
            $itemColumns = $this->getAttributes('shipment_items');
            $collection->walk(function ($item) use (&$result, $itemColumns) {
                if (count($itemColumns)) {
                    $items = $item->getItems(true);
                    $items->columns($itemColumns);
                    $items->load(true, true);
                }
                $result[] = $item->toArray() + ['items' => isset($item) ? $items->toArray() : []];
            });
            return $result;
        }
        return $this->getResponse()->withStatus(403);
    }

    public function getShipmentTrack()
    {
        $data = $this->getRequest()->getQuery();
        $columns = $this->getAttributes('shipment_track');
        if (count($columns)) {
            if ($this->authOptions['validation'] > 0) {
                if (isset($data['openId']) && $data['openId'] === $this->authOptions['open_id']) {
                    $token = new Token;
                    $token->load($data['openId'], 'open_id');
                    unset($data['openId']);
                    $shipment = new ShipmentCollection;
                    $shipment->columns(['id'])
                            ->where(['customer_id' => $token['customer_id']]);
                } else {
                    return $this->getResponse()->withStatus(400);
                }
            }
            $collection = new TrackCollection;
            $collection->columns($columns);
            $this->filter($collection, $data);
            if ($shipment) {
                $collection->in('shipment_id', $shipment);
            }
            $collection->load(true, true);
            return $collection->toArray();
        }
        return $this->getResponse()->withStatus(403);
    }

    public function putShipmentTrack()
    {
        if ($this->authOptions['validation'] === -1) {
            $attributes = $this->getAttributes(Customer::ENTITY_TYPE, false);
            $data = $this->getRequest()->getPost();
            $set = [];
            foreach ($attributes as $attribute) {
                if (isset($data[$attribute])) {
                    $set[$attribute] = $data[$attribute];
                }
            }
            if ($set) {
                $model = new Track;
                $model->setData($set);
                $model->save([], true);
                return $this->getResponse()->withStatus(202);
            }
        }
        return $this->getResponse()->withStatus(403);
    }

}
