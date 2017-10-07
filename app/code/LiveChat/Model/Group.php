<?php

namespace Seahinet\LiveChat\Model;

use Seahinet\Customer\Model\Collection\Customer;
use Seahinet\Lib\Model\AbstractModel;

class Group extends AbstractModel
{

    protected function construct()
    {
        $this->init('livechat_group', 'id', ['id', 'name', 'session_id']);
    }

    public function getMembers($idOnly = true)
    {
        $tableGateway = $this->getTableGateway('livechat_group_member');
        $select = $tableGateway->getSql()->select();
        $select->columns(['customer_id'])
                ->where(['group_id' => $this->getId()]);
        if ($idOnly) {
            $resultSet = $tableGateway->selectWith($select)->toArray();
            $result = [];
            array_walk($resultSet, function($item) use (&$result) {
                $result[] = $item['customer_id'];
            });
            return $result;
        } else {
            $customer = new Customer;
            $customer->in('id', $select);
            return $customer;
        }
    }

}
