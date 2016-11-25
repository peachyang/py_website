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
        $replace = base64_encode(json_encode([
            '{url}' => rawurlencode($this->getRequest()->getUri()->__toString()),
            '{title}' => rawurlencode(Head::instance()->getTitle()),
            '{image}' => rawurlencode($this->getBaseUrl('pub/resource/image/resized/704x' . $this->getProduct()->getThumbnail()))
        ]));
        return $this->getBaseUrl('catalog/product/share/?media_id=' . $media['id'] . '&product_id=' . $this->getProduct()->getId() . '&params=' . $replace);
    }

}
