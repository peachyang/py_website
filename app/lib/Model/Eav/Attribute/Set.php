<?php

namespace Seahinet\Lib\Model\Eav\Attribute;

use Seahinet\Lib\Model\AbstractModel;

class Set extends AbstractModel
{

    protected function construct()
    {
        $this->init('eav_attribute_set', 'id', ['id', 'type_id', 'name']);
    }

    public function getLabel($language = false)
    {
        if ($this->getId()) {
            $tableGateway = $this->getTableGateway('eav_attribute_set_label');
            $select = $tableGateway->getSql()->select();
            $select->columns(['label', 'language_id'])
                    ->where(['attribute_set_id' => $this->getId()]);
            if ($language) {
                $languageId = is_array($language) || $language instanceof Language ? $language['id'] : $language;
                $select->where(['language_id' => $languageId]);
            }
            $result = $tableGateway->selectWith($select)->toArray();
            if ($language === false) {
                $label = [];
                foreach ($result as $item) {
                    $label[$item['language_id']] = $item['label'];
                }
                return $label;
            }
            return count($result) ? $result[0]['label'] : '';
        }
        return '';
    }

    protected function beforeSave()
    {
        $this->beginTransaction();
        parent::beforeSave();
    }

    protected function afterSave()
    {
        parent::afterSave();
        $tableGateway = $this->getTableGateway('eav_entity_attribute');
        $tableGateway->delete(['attribute_set_id' => $this->getId()]);
        if (!empty($this->storage['attributes'])) {
            foreach ($this->storage['attributes'] as $groupId => $attributes) {
                foreach ($attributes as $sortOrder => $attributeId) {
                    $tableGateway->insert([
                        'attribute_set_id' => $this->getId(),
                        'attribute_group_id' => $groupId,
                        'attribute_id' => $attributeId,
                        'sort_order' => $sortOrder
                    ]);
                }
            }
        }
        $this->flushList('eav_attribute');
        if (isset($this->storage['name']) && isset($this->storage['label'])) {
            $tableGateway = $this->getTableGateway('eav_attribute_set_language');
            foreach ((array) $this->storage['name'] as $languageId => $name) {
                $this->upsert(['name' => $name], ['attribute_set_id' => $this->getId(), 'language_id' => $languageId], $tableGateway);
            }
        }
        $this->commit();
    }

    protected function beforeLoad($select)
    {
        $select->join('eav_attribute_set_language', 'eav_attribute_set_language.attribute_set_id=eav_attribute_set.id', ['name'], 'left');
        $select->join('core_language', 'eav_attribute_set_language.language_id=core_language.id', ['language_id' => 'id', 'language' => 'name'], 'left');
        parent::beforeLoad($select);
    }

    protected function afterLoad(&$result)
    {
        if (isset($result[0])) {
            $language = [];
            $name = [];
            foreach ($result as $item) {
                $language[$item['language_id']] = $item['language'];
                $name[$item['language_id']] = $item['name'];
            }
            $result[0]['language'] = $language;
            $result[0]['language_id'] = array_keys($language);
            $result[0]['name'] = $name;
        }
        parent::afterLoad($result);
    }

}
