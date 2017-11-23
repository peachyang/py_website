<?php

namespace Seahinet\Lib\Model\Eav\Attribute;

use Exception;
use Seahinet\Lib\Model\{
AbstractModel,
    Language
};
use \Zend\Db\Sql\Where;

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
        $this->commit();
    }

}
