<?php

namespace Seahinet\Promotion\Model;

use Seahinet\Lib\Model\AbstractModel;

class Coupon extends AbstractModel
{

    protected $rule = [];

    protected function construct()
    {
        $this->init('promotion_coupon', 'id', ['id', 'code', 'promotion_id', 'status']);
    }

    public function getRule()
    {
        if (empty($this->rule) && !empty($this->storage['promotion_id'])) {
            $this->rule = new Rule;
            $this->rule->load($this->storage['promotion_id']);
        }
        return $this->rule;
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
            if (!empty($this->getRule()['uses_per_coupon'])) {
                $log = $tableGateway->select(['coupon_id' => $this->getId()])->toArray();
                if (count($log) >= $this->getRule()['uses_per_coupon']) {
                    $this->setData('status', 0)->save();
                }
            }
        }
        return $this;
    }

}
