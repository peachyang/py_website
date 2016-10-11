<?php

namespace Seahinet\Payment\Model;

use Seahinet\Payment\ViewModel\SavedCc as ViewModel;

class SavedCc extends AbstractMethod
{

    const METHOD_CODE = 'saved_cc';

    public function getDescription()
    {
        return new ViewModel;
    }

}
