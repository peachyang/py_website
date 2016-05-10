<?php

namespace Seahinet\Cms\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Category extends AbstractCollection
{

    protected function construct()
    {
        $this->init('cms_category');
    }

}
