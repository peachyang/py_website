<?php

namespace Seahinet\Customer\Model;

use Seahinet\Lib\Model\AbstractModel;

class CreditCard extends AbstractModel
{

    protected function construct()
    {
        $this->init('customer_credit_card', 'id', ['id', 'customer_id', 'name', 'type', 'number', 'exp_month', 'exp_year', 'verification']);
    }

}
