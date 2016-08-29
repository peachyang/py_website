<?php

namespace Seahinet\Promotion\Model\Condition;

interface ConditionInterface
{

    /**
     * @param mixed $model
     * @param \Seahinet\Promotion\Model\Condition $condition
     * @param int $storeId
     * @return bool
     */
    public function match($model, $condition, $storeId);
}
