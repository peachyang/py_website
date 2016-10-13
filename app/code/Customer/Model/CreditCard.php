<?php

namespace Seahinet\Customer\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Payment\Source\CcType;

class CreditCard extends AbstractModel
{

    protected $type;

    protected function construct()
    {
        $this->init('customer_credit_card', 'id', ['id', 'customer_id', 'name', 'type', 'number', 'exp_month', 'exp_year', 'verification']);
        $this->type = (new CcType)->getSourceArray();
    }

    public function getType()
    {
        return $this->type[$this->storage['type']];
    }

}
