<?php

namespace Seahinet\Catalog\ViewModel\Product;

use Seahinet\Customer\Model\Collection\Media;
use Seahinet\Lib\ViewModel\Head;

class Share extends View
{

    public function getMedia()
    {
        return new Media;
    }

    public function getLink($media)
    {
        return $this->getBaseUrl('catalog/product/share/?media_id=' . $media['id'] . '&product_id=' . $this->getProduct()->getId());
    }

}
