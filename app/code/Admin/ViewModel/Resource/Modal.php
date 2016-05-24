<?php

namespace Seahinet\Admin\ViewModel\Resource;

use Seahinet\Resource\Source\Category;
use Seahinet\Lib\ViewModel\AbstractViewModel;

class Modal extends AbstractViewModel
{

    public function getCategorySource()
    {
        return (new Category)->getSourceArray();
    }

}
