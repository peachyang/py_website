<?php

namespace Seahinet\Retailer\ViewModel\Dashboard;

use Seahinet\Retailer\ViewModel\AbstractViewModel;
use Seahinet\Sales\Model\Collection\Order;
use Zend\Db\Sql\Expression;

class Profile extends AbstractViewModel
{

    public function getPendingPayment()
    {
        $collection = new Order;
        $collection->columns(['count' => new Expression('count(sales_order.id)')])
                ->join('sales_order_status', 'sales_order_status.id=sales_order.status_id', [], 'left')
                ->join('sales_order_phase', 'sales_order_status.phase_id=sales_order_phase.id', [], 'left')
                ->where([
                    '(sales_order_phase.code=\'pending_payment\' OR sales_order_phase.code=\'pending\')',
                    'store_id' => $this->getStore()->getId()
                ])
                ->group('sales_order_phase.id');
        $collection->load(true, true);
        $count = 0;
        foreach ($collection as $item) {
            $count += $item['count'];
        }
        return $count;
    }

    public function getProcessing()
    {
        $collection = new Order;
        $collection->columns(['count' => new Expression('count(sales_order.id)')])
                ->join('sales_order_status', 'sales_order_status.id=sales_order.status_id', [], 'left')
                ->join('sales_order_phase', 'sales_order_status.phase_id=sales_order_phase.id', [], 'left')
                ->where([
                    'sales_order_phase.code' => 'processing',
                    'store_id' => $this->getStore()->getId()
                ])->group('sales_order_phase.id');
        $collection->load(true, true);
        return count($collection) ? $collection[0]['count'] : 0;
    }

    public function getHolding()
    {
        $collection = new Order;
        $collection->columns(['count' => new Expression('count(sales_order.id)')])
                ->join('sales_order_status', 'sales_order_status.id=sales_order.status_id', [], 'left')
                ->join('sales_order_phase', 'sales_order_status.phase_id=sales_order_phase.id', [], 'left')
                ->where([
                    'sales_order_phase.code' => 'holded',
                    'store_id' => $this->getStore()->getId()
                ]);
        $collection->load(true, true);
        return count($collection) ? $collection[0]['count'] : 0;
    }

}
