<?php

namespace Seahinet\Sales\Model\Api\Rest;

use Seahinet\Api\Model\Api\Rest\AbstractHandler;
use Seahinet\Oauth\Model\Token;
use Seahinet\Sales\Model\Collection\{
    CreditMemo as CreditMemoCollection,
    Order as OrderCollection
};

class CreditMemo extends AbstractHandler
{

    public function getCreditMemo()
    {
        $data = $this->getRequest()->getQuery();
        $columns = $this->getAttributes('creditmemo');
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
        return $this->getResponse()->withStatus(403);
    }

}
