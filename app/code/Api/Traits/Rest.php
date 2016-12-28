<?php

namespace Seahinet\Api\Traits;

trait Rest
{

    use \Seahinet\Lib\Traits\Filter,
        \Seahinet\Catalog\Traits\Rest,
        \Seahinet\Customer\Traits\Rest,
        \Seahinet\Sales\Traits\Rest;
}
