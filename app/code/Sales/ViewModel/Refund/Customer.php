<?php

namespace Seahinet\Sales\ViewModel\Refund;

use Seahinet\Customer\ViewModel\Account;
use Seahinet\Sales\Model\Collection\Rma;
use Seahinet\Sales\Source\Refund\{
    Service,
    Status
};

class Customer extends Account
{

    protected $status = null;
    protected $service = null;

    public function getApplication()
    {
        $collection = new Rma;
        $collection->where(['customer_id' => $this->getCustomer()->getId()]);
        return $collection;
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
        if (in_array($template, [
                    '1-1', '2-2', '2-4'
                ])) {
            $viewModel = new static;
            $viewModel->setTemplate('sales/refund/handler/' . $template);
        }
        return $viewModel ?? '';
    }

}
