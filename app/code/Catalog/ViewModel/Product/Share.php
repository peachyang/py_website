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

    public function getLink($link)
    {
        preg_match_all('/\{([^\}]+)\}/', $link, $matches);
        $replace = [
            '{url}' => rawurlencode($this->getRequest()->getUri()->__toString()),
            '{title}' => rawurlencode(Head::instance()->getTitle()),
            '{image}' => rawurlencode($this->getBaseUrl('pub/resource/image/resized/704x' . $this->getProduct()->getThumbnail()))
        ];
        foreach ($matches[1] as $attr) {
            $value = $this->getProduct()->offsetGet($attr);
            if (is_scalar($value)) {
                $replace['{' . $attr . '}'] = rawurlencode($value);
            }
        }
        return str_replace(array_keys($replace), array_values($replace), $link);
    }

}
