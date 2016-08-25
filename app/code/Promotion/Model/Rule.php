<?php

namespace Seahinet\Promotion\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Promotion\Model\Collection\Coupon as CouponCollection;

class Rule extends AbstractModel
{

    protected function construct()
    {
        $this->init('promotion', 'id', ['id', 'name', 'description', 'store_id', 'status', 'use_coupon', 'from_date', 'to_date', 'stop_processing', 'sort_order']);
    }

    public function getCoupon()
    {
        if (!empty($this->storage['use_coupon'])) {
            $collection = new CouponCollection;
            $collection->where('promotion_id', $this->getId());
            return $collection;
        }
        return [];
    }

    protected function beforeSave()
    {
        $this->beginTransaction();
        parent::beforeSave();
    }

    protected function afterSave()
    {
        if (!empty($this->storage['coupon'])) {
            foreach ($this->storage['coupon']['code'] as $key => $code) {
                $coupon = new Coupon;
                $coupon->load($code, 'code');
                if (!$coupon->getId()) {
                    $coupon->setData([
                        'id' => null,
                        'promotion_id' => $this->getId(),
                        'code' => $code,
                        'uses_per_coupon' => $this->storage['coupon']['uses_per_coupon'][$key]? : 0,
                        'uses_per_customer' => $this->storage['coupon']['uses_per_customer'][$key]? : 0,
                        'status' => 1
                    ])->save();
                }
            }
        }
        parent::afterSave();
        $this->commit();
    }

}
