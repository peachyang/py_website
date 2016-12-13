<?php

namespace Seahinet\Promotion\Model;

use Seahinet\Lib\Model\AbstractModel;

class Coupon extends AbstractModel
{

    protected function construct()
    {
        $this->init('promotion_coupon', 'id', ['id', 'code', 'promotion_id', 'status']);
    }

    public function apply($orderId, $customerId = null)
    {
        if ($this->getId()) {
            $tableGateway = $this->getTableGateway('promotion_coupon_log');
            $tableGateway->insert([
                'coupon_id' => $this->getId(),
                'order_id' => $orderId,
                'customer_id' => $customerId
            ]);
            if ($this->storage['uses_per_coupon'] > 0) {
                $log = $tableGateway->select(['coupon_id' => $this->getId()])->toArray();
                if (count($log) >= $this->storage['uses_per_coupon']) {
                    $this->setData('status', 0)->save();
                }
            }
        }
        return $this;
    }

}
