<?php

namespace Seahinet\Customer\Model;

use Seahinet\I18n\Model\Locate;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\Model\Eav\Entity;

class Address extends Entity
{

    const ENTITY_TYPE = 'address';

    protected function construct()
    {
        $this->init('id', ['id', 'type_id', 'attribute_set_id', 'store_id', 'customer_id', 'status']);
    }

    public function __toString()
    {
        return $this->display();
    }

    public function display($inOneLine = true)
    {
        $format = $this->getContainer()->get('config')[$inOneLine ? 'customer/address/one_line' : 'customer/address/multi_line'];
        $replace = [];
        $language = Bootstrap::getLanguage();
        $locate = new Locate;
        preg_match_all('#\{\{[^\}]+\}\}#', $format, $matches);
        foreach ($matches[0] as $match) {
            $src = trim($match, '{}');
            if (strpos($match, 'label:') === false) {
                $target = (isset($this->storage[$src]) ? (is_numeric($this->storage[$src]) ?
                                        $locate->getLabel($src, $this->storage[$src])[$this->storage[$src]]->getName($language->offsetGet('code')) :
                                        $this->storage[$src]) : '');
            } else {
                $attribute = new Attribute;
                $attribute->withLabel($language->getId())
                        ->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [], 'left')
                        ->where(['eav_attribute.code' => substr($src, 6), 'eav_entity_type.code' => self::ENTITY_TYPE]);
                $target = $attribute->count() ? $attribute[0]['label'] : '';
            }
            $replace[$match] = $target;
        }
        return str_replace(array_keys($replace), array_values($replace), $format);
    }

}
