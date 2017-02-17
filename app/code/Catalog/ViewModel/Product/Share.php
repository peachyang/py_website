<?php

namespace Seahinet\Catalog\ViewModel\Product;

use Seahinet\Customer\Model\Collection\Media;

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

    public function getSharingUrl()
    {
        $uri = $this->getUri()->withFragment('');
        if ($this->getSegment('customer')->get('hasLoggedIn')) {
            $uri = $uri->withQuery('referer=' . $this->getSegment('customer')->get('customer')->offsetGet('increment_id'));
        }
        return $uri->__toString();
    }

}
