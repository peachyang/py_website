<?php

namespace Seahinet\Lib\Model\Eav;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Lib\Model\Language;
use Zend\Db\TableGateway\TableGateway;

class Attribute extends AbstractModel
{

    protected function construct()
    {
        $this->init('eav_attribute', 'id', ['id', 'type_id', 'attribute_set_id', 'attribute_group_id', 'code', 'type', 'input', 'validation', 'is_required', 'default_value', 'is_unique', 'sort_order']);
    }

    public function getOptions($language)
    {
        if ($this->getId() && isset($this->storage['input']) && in_array($this->storage['input'], ['select', 'radio', 'checkbox'])) {
            $languageId = is_array($language) || $language instanceof Language ? $language['id'] : $language;
            $tableGateway = new TableGateway('eav_attribute_option');
            $select = $tableGateway->getSql()->select();
            $select->join('eav_attribute_option_value', 'eav_attribute_option_value.option_id=eav_attribute_option.id', ['label'], 'left')
                    ->order('sort_order')
                    ->columns(['id'])
                    ->where(['attribute_id' => $this->getId(), 'language_id' => $languageId]);
            $result = $tableGateway->selectWith($select)->toArray();
            $options = [];
            array_walk($result, function($item) use (&$options){
                $options[$item['id']] = $options['label'];
            });
            return $options;
        }
        return [];
    }

    public function getLabel($language)
    {
        if ($this->getId()) {
            $languageId = is_array($language) || $language instanceof Language ? $language['id'] : $language;
            $tableGateway = new TableGateway('eav_attribute_label');
            $select = $tableGateway->getSql()->select();
            $select->columns(['label'])
                    ->where(['attribute_id' => $this->getId(), 'language_id' => $languageId]);
            $result = $tableGateway->selectWith($select)->toArray();
            return count($result) ? $result[0]['label'] : '';
        }
        return '';
    }

}
