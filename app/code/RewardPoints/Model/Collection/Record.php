<?php

namespace Seahinet\RewardPoints\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Record extends AbstractCollection
{

    protected function construct()
    {
        $this->init('reward_points');
    }

}
