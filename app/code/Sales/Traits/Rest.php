<?php

namespace Seahinet\Sales\Traits;

use Seahinet\Sales\Model\Collection\{
    CreditMemo as CreditMemoCollection,
    Invoice as InvoiceCollection,
    Order as OrderCollection,
    Shipment as ShipmentCollection
};

trait Rest
{

    protected function getOrder()
    {
        $data = $this->getRequest()->getQuery();
        $columns = $this->getAttributes('order');
        if (count($columns)) {
            if ($this->authOptions['role_id'] > 0) {
                if (isset($data['openId']) && $data['openId'] === $this->authOptions['open_id']) {
                    $token = new Token;
                    $token->load($data['openId'], 'open_id');
                    unset($data['openId']);
                    $data['customer_id'] = $token['customer_id'];
                } else {
                    return $this->getResponse()->withStatus(400);
                }
            }
            $collection = new OrderCollection;
            $collection->columns($columns);
            $this->filter($collection, $data);
            $result = [];
            $itemColumns = $this->getAttributes('order_items');
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
        return $this->getResponse()->withStatus(400);
    }

    protected function getInvoice()
    {
        $data = $this->getRequest()->getQuery();
        $columns = $this->getAttributes('invoice');
        if (count($columns)) {
            if ($this->authOptions['role_id'] > 0) {
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
            $collection = new InvoiceCollection;
            $collection->columns($columns);
            $this->filter($collection, $data);
            if ($order) {
                $collection->in('order_id', $order);
            }
            $result = [];
            $itemColumns = $this->getAttributes('invoice_items');
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
        return $this->getResponse()->withStatus(400);
    }

    protected function getCreditMemo()
    {
        $data = $this->getRequest()->getQuery();
        $columns = $this->getAttributes('creditmemo');
        if (count($columns)) {
            if ($this->authOptions['role_id'] > 0) {
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
            $collection = new CreditMemoCollection;
            $collection->columns($columns);
            $this->filter($collection, $data);
            if ($order) {
                $collection->in('order_id', $order);
            }
            $result = [];
            $itemColumns = $this->getAttributes('creditmemo_items');
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
        return $this->getResponse()->withStatus(400);
    }

    protected function getShipment()
    {
        $data = $this->getRequest()->getQuery();
        $columns = $this->getAttributes('shipment');
        if (count($columns)) {
            if ($this->authOptions['role_id'] > 0) {
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
        return $this->getResponse()->withStatus(400);
    }

    protected function getShipmentTrack()
    {
        
    }

    protected function deleteShipmentTrack()
    {
        
    }

    protected function putShipmentTrack()
    {
        
    }

}
