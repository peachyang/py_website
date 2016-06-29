<?php

namespace Seahinet\Lib\Model\Collection\Eav;

use Seahinet\Lib\Model\Language;
use Seahinet\Lib\Model\AbstractCollection;

class Attribute extends AbstractCollection
{

    protected $hasSortOrder = false;

    protected function construct()
    {
        $this->init('eav_attribute');
    }

    public function withLabel($language)
    {
        $languageId = is_array($language) || $language instanceof Language ? $language['id'] : $language;
        $this->select->join('eav_attribute_label', 'eav_attribute.id=eav_attribute_label.attribute_id', ['label'], 'left')
                ->where(['eav_attribute_label.language_id' => $languageId]);
        return $this;
    }

    public function withGroup()
    {
        if (!$this->hasSortOrder) {
            $this->select->join('eav_entity_attribute', 'eav_entity_attribute.attribute_id=eav_attribute.id', ['sort_order'], 'left');
            $this->hasSortOrder = true;
        }
        $this->select->join('eav_attribute_group', 'eav_entity_attribute.attribute_group_id=eav_attribute_group.id', ['attribute_group' => 'name', 'attribute_group_id' => 'id'], 'left');
        return $this;
    }

    public function withSet()
    {
        if (!$this->hasSortOrder) {
            $this->select->join('eav_entity_attribute', 'eav_entity_attribute.attribute_id=eav_attribute.id', ['sort_order'], 'left');
            $this->hasSortOrder = true;
        }
        $this->select->join('eav_attribute_set', 'eav_entity_attribute.attribute_set_id=eav_attribute_set.id', ['attribute_set' => 'name', 'attribute_set_id' => 'id'], 'left');
        return $this;
    }

}
