<?php

namespace Seahinet\Promotion\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Promotion\Model\Collection\Condition as Collection;

class Condition extends AbstractModel
{

    protected function construct()
    {
        $this->init('promotion_condition', 'id', ['id', 'promotion_id', 'parent_id', 'identifier', 'operator', 'value']);
    }

    public function getChildren()
    {
        if ($this->getId()) {
            $collection = new Collection;
            $collection->where(['parent_id' => $this->getId()]);
            return $collection;
        }
        return [];
    }

    public function getConditionClass($identifier = null)
    {
        if (is_null($identifier)) {
            $identifier = $this->storage['identifier'];
        }
        $className = '\\Seahinet\\Promotion\\Model\\Condition\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $identifier)));
        if (is_subclass_of($className, '\\Seahinet\\Promotion\\Model\\Condition\\ConditionInterface')) {
            return new $className;
        }
        return null;
    }

    public function match($model, $storeId)
    {
        if ($this->getId()) {
            return $this->getConditionClass()->match($model, $this, $storeId);
        }
        return false;
    }

}
