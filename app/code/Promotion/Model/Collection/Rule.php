<?php

namespace Seahinet\Promotion\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Rule extends AbstractCollection
{

    protected function construct()
    {
        $this->init('promotion');
        $this->select->join('promotion_in_store', 'promotion_in_store.promotion_id=promotion.id', ['store_id'], 'left');
    }

    protected function afterLoad(&$result)
    {
        $data = [];
        foreach ($result as $key => $item) {
            if (isset($item['id'])) {
                if (!isset($data[$item['id']])) {
                    $data[$item['id']] = $item;
                    $data[$item['id']]['store_id'] = [];
                }
                if (!empty($item['store_id'])) {
                    $data[$item['id']]['store_id'][] = $item['store_id'];
                }
            }
        }
        $result = array_values($data);
        parent::afterLoad($result);
    }

}
