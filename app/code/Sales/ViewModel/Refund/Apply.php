<?php

namespace Seahinet\Sales\ViewModel\Refund;

use Seahinet\Lib\ViewModel\Template;
use Seahinet\Sales\Source\RefundReason;

class Apply extends Template
{

    public function getCurrency()
    {
        return $this->getContainer()->get('currency');
    }

    public function getReasons()
    {
        return (new RefundReason)->getSourceArray();
    }

}
