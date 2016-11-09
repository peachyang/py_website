<?php

namespace Seahinet\Sales\Source\Refund;

use Seahinet\Lib\Source\SourceInterface;

class Status implements SourceInterface
{

    public function getSourceArray($service = 0)
    {
        if ($service == -1) {
            return [
                0 => 'Applied',
                -1 => 'Refused'
            ];
        }
        $status = [
            0 => 'Applied',
            1 => 'Approved'
        ];
        if ($service == 2) {
            $status += [
                2 => 'Delivering',
                3 => 'Repairing',
                4 => 'Delivering'
            ];
        } else if ($service == 1) {
            $status += [
                2 => 'Delivering',
                3 => 'Confirming',
                4 => 'Refunding'
            ];
        }
        $status[5] = 'Complete';
        return $status;
    }

}
