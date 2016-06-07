<?php

namespace Seahinet\Lib\Model\Collection\Eav;

use Seahinet\Lib\Model\Language;
use Seahinet\Lib\Model\AbstractCollection;

class Attribute extends AbstractCollection
{

    protected function construct()
    {
        $this->init('eav_attribute');
    }

    public function withLabel($language)
    {
        $languageId = is_array($language) || $language instanceof Language ? $language['id'] : $language;
        $this->select->join('eav_attribute_label', 'eav_attribute.id=eav_attribute_label.attribute_id', ['label'], 'left')
                ->where('eav_attribute_label.language_id=' . $languageId);
    }

    public function withGroup()
    {
        $this->select->join('eav_attribute_group', 'eav_attribute.attribute_group_id=eav_attribute_group.id', ['attribute_group' => 'name', 'attribute_group_id' => 'id'], 'left');
    }

    public function withSet()
    {
        $this->select->join('eav_attribute_set', 'eav_attribute.attribute_set_id=eav_attribute_set.id', ['attribute_set' => 'name', 'attribute_set_id' => 'id'], 'left');
    }
    
}
