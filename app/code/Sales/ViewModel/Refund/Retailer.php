<?php

namespace Seahinet\Sales\ViewModel\Refund;

use Seahinet\Retailer\ViewModel\AbstractViewModel;
use Seahinet\Sales\Model\Collection\Rma;
use Seahinet\Sales\Source\Refund\{
    Service,
    Status
};

class Retailer extends AbstractViewModel
{

    protected $status = null;
    protected $service = null;

    public function getApplication()
    {
        $collection = new Rma;
        $collection->join('sales_order', 'sales_order.id=sales_rma.order_id', [], 'left')
                ->join('retailer', 'sales_order.store_id=retailer.store_id', [], 'left')
                ->where(['retailer.id' => $this->getRetailer()->getId()]);
        return $collection;
    }

    public function getCurrency()
    {
        return $this->getContainer()->get('currency');
    }

    public function getStatus($key)
    {
        if (is_null($this->status)) {
            $this->status = (new Status)->getSourceArray();
        }
        return $this->status[$key] ?? '';
    }

    public function getService($key)
    {
        if (is_null($this->service)) {
            $this->service = (new Service)->getSourceArray();
        }
        return $this->service[$key] ?? '';
    }

    public function getHandler($service, $status)
    {
        $template = $service . '-' . $status;
        if (!in_array($template, [
                    '1-2', '2-2', '2-4'
                ])) {
            $viewModel = new static;
            $viewModel->setTemplate('sales/refund/handler/' . $template);
        }
        return $viewModel ?? '';
    }

}
