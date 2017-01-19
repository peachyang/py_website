<?php

namespace Seahinet\LiveChat\Model;

use Seahinet\Lib\Model\AbstractModel;

class Group extends AbstractModel
{

    protected function construct()
    {
        $this->init('livechat_group', 'id', ['id', 'name', 'session_id']);
    }

    public function getMembers()
    {
        $tableGateway = $this->getTableGateway('livechat_group_member');
        $resultSet = $tableGateway->select(['group_id' => $this->getId()])->toArray();
        $result = [];
        array_walk($resultSet, function($item) use (&$result) {
            $result[] = $item['customer_id'];
        });
        return $result;
    }

}
