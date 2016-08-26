<?php

namespace Seahinet\Promotion\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Promotion\Model\Collection\Coupon as CouponCollection;
use Seahinet\Promotion\Model\Collection\Condition as ConditionCollection;
use Zend\Db\TableGateway\TableGateway;

class Rule extends AbstractModel
{

    protected function construct()
    {
        $this->init('promotion', 'id', [
            'id', 'name', 'description', 'store_id', 'status', 'use_coupon',
            'from_date', 'to_date', 'stop_processing', 'qty', 'price', 'is_fixed',
            'per_item', 'free_shipping', 'apply_to', 'sort_order'
        ]);
    }

    public function getCoupon()
    {
        if ($this->getId() && !empty($this->storage['use_coupon'])) {
            $collection = new CouponCollection;
            $collection->where('promotion_id', $this->getId());
            return $collection;
        }
        return [];
    }

    public function getCondition()
    {
        if ($this->getId()) {
            $collection = new ConditionCollection;
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
        if (!empty($this->storage['condition'])) {
            $tableGateway = new TableGateway('promotion_condition', $this->getContainer()->get('dbAdapter'));
            $tableGateway->delete(['promotion_id' => $this->getId()]);
            $pids = [];
            foreach ($this->prepareTree($this->storage['condition']) as $id => $data) {
                $pid = isset($pids[$data['pid']]) ? $pids[$data['pid']] : null;
                $model = new Condition;
                $model->setData(['parent_id' => $pid, 'promotion_id' => $this->getId()] + $data)->save();
                $pids[$id] = $model->getId();
            }
        }
        if (!empty($this->storage['handler'])) {
            $tableGateway = new TableGateway('promotion_handler', $this->getContainer()->get('dbAdapter'));
            $tableGateway->delete(['promotion_id' => $this->getId()]);
            $pids = [];
            foreach ($this->prepareTree($this->storage['handler']) as $id => $data) {
                $pid = isset($pids[$data['pid']]) ? $pids[$data['pid']] : null;
                $model = new Handler;
                $model->setData(['parent_id' => $pid, 'promotion_id' => $this->getId()] + $data)->save();
                $pids[$id] = $model->getId();
            }
        }
        parent::afterSave();
        $this->commit();
    }

    private function prepareTree($tree)
    {
        $array = [];
        if (count($tree['identifier']) > 1) {
            foreach ($tree['identifier'] as $key => $identifier) {
                $array[$key] = [
                    'pid' => isset($tree['pid'][$key]) ? (int) $tree['pid'][$key] : null,
                    'identifier' => $identifier,
                    'operator' => $tree['operator'][$key],
                    'value' => empty($tree['value'][$key]) ? null : $tree['value'][$key]
                ];
            }
            uasort($array, function($a, $b) {
                return $a['pid'] == $b['pid'] ? 0 : ($a['pid'] > $b['pid'] ? 1 : -1);
            });
        }
        return $array;
    }

}
