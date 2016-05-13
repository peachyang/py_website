<?php

namespace Seahinet\Lib\Model;

use Seahinet\Lib\Model\Collection;
use Zend\Db\Sql\Predicate\Operator;

class Merchant extends AbstractModel
{

    protected function construct()
    {
        $this->init('core_merchant', 'id', ['id', 'code', 'is_default', 'status']);
    }

    public function getStore($code = null)
    {
        $store = new Collection\Store;
        if (!is_null($code)) {
            $store->where(['merchant_id' => $this->getId(), 'code' => $code, 'status' => 1]);
        } else {
            $store->where(['merchant_id' => $this->getId(), 'is_default' => 1, 'status' => 1]);
        }
        $store->load();
        return count($store) ? new Store($store[0]) : null;
    }

    public function getLanguage($code = null)
    {
        $lang = new Collection\Language;
        if (!is_null($code)) {
            $lang->where(['merchant_id' => $this->getId(), 'code' => $code, 'status' => 1]);
        } else {
            $lang->where(['merchant_id' => $this->getId(), 'is_default' => 1, 'status' => 1]);
        }
        $lang->load();
        return count($lang) ? new Language($lang[0]) : null;
    }

    protected function afterSave()
    {
        if ($this->storage['is_default']) {
            $collection = new Collection\Merchant;
            $collection->where(['is_default' => 1])
                    ->where(new Operator('id', Operator::OPERATOR_NOT_EQUAL_TO, $this->getId()));
            foreach ($collection as $item) {
                $model = new static;
                $model->setData([
                    'id' => $item['id'],
                    'is_default' => 0
                ])->save();
            }
        }
        parent::afterSave();
    }

}
