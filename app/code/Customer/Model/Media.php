<?php

namespace Seahinet\Customer\Model;

use Seahinet\Catalog\Model\Product;
use Seahinet\Lib\Model\AbstractModel;

class Media extends AbstractModel
{

    protected function construct()
    {
        $this->init('social_media', 'id', ['id', 'label', 'link', 'icon']);
    }

    public function getUrl($replace = [], $product = false)
    {
        if (!empty($this->storage['link'])) {
            if (empty($product)) {
                return preg_replace('/\{([^\}]+)\}/', '', $this->storage['link']);
            }
            if (is_scalar($product)) {
                $product = (new Product)->load($product);
            }
            preg_match_all('/\{([^\}]+)\}/', $this->storage['link'], $matches);
            foreach ($matches[1] as $attr) {
                $value = $product->offsetGet($attr);
                if (is_scalar($value)) {
                    $replace['{' . $attr . '}'] = rawurlencode($value);
                }
            }
            return str_replace(array_keys($replace), array_values($replace), $this->storage['link']);
        }
        return '/';
    }

}
