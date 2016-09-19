<?php

namespace Seahinet\Lib\Model\Eav;

use Exception;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Lib\Model\Language;
use Zend\Db\TableGateway\TableGateway;

class Attribute extends AbstractModel
{

    protected function construct()
    {
        $this->init('eav_attribute', 'id', ['id', 'type_id', 'code', 'type', 'input', 'validation', 'is_required', 'default_value', 'is_unique', 'searchable', 'filterable', 'comparable', 'sortable']);
    }

    public function getOption($option, $language = false)
    {
        if ($this->getId() && isset($this->storage['input']) && in_array($this->storage['input'], ['select', 'radio', 'checkbox', 'multiselect'])) {
            $tableGateway = new TableGateway('eav_attribute_option', $this->getContainer()->get('dbAdapter'));
            $select = $tableGateway->getSql()->select();
            $select->join('eav_attribute_option_label', 'eav_attribute_option_label.option_id=eav_attribute_option.id', ['label'], 'left')
                    ->order('sort_order')
                    ->columns(['id', 'sort_order'])
                    ->where(['attribute_id' => $this->getId()] +
                            (is_numeric($option) || !is_scalar($option) ?
                                    ['eav_attribute_option.id' => is_scalar($option) ? $option : $option['id']] :
                                    ['eav_attribute_option_label.label' => $option]));
            if (!$language) {
                $languageId = Bootstrap::getLanguage()->getId();
            } else {
                $languageId = is_array($language) || $language instanceof Language ? $language['id'] : $language;
            }
            $select->where(['language_id' => $languageId]);
            $result = $tableGateway->selectWith($select)->toArray();
            if (count($result)) {
                return is_numeric($option) || !is_scalar($option) ? $result[0]['label'] : $result[0]['id'];
            }
        }
        return '';
    }

    public function getOptions($language = false)
    {
        if ($this->getId() && isset($this->storage['input']) && in_array($this->storage['input'], ['select', 'radio', 'checkbox', 'multiselect'])) {
            if (empty($this->storage['source']) || !is_subclass_of($this->storage['source'], '\\Seahinet\\Lib\\Source\\SourceInterface')) {
                $tableGateway = new TableGateway('eav_attribute_option', $this->getContainer()->get('dbAdapter'));
                $select = $tableGateway->getSql()->select();
                $select->join('eav_attribute_option_label', 'eav_attribute_option_label.option_id=eav_attribute_option.id', ['label', 'language_id'], 'left')
                        ->order('sort_order')
                        ->columns(['id', 'sort_order'])
                        ->where(['attribute_id' => $this->getId()]);
                if ($language) {
                    $languageId = is_array($language) || $language instanceof Language ? $language['id'] : $language;
                    $select->where(['language_id' => $languageId]);
                }
                $result = $tableGateway->selectWith($select)->toArray();
                $options = $language ? [] : ['order' => []];
                foreach ($result as $item) {
                    if ($language === false) {
                        if (!isset($options[$item['id']])) {
                            $options[$item['id']] = [];
                        }
                        $options[$item['id']][$item['language_id']] = $item['label'];
                        $options['order'][$item['id']] = $item['sort_order'];
                    } else {
                        $options[$item['id']] = $item['label'];
                    }
                }
                return $options;
            } else {
                return (new $this->storage['source'])->getSourceArray();
            }
        }
        return $language ? [] : ['order' => []];
    }

    public function getLabel($language = false)
    {
        if ($this->getId()) {
            $tableGateway = new TableGateway('eav_attribute_label', $this->getContainer()->get('dbAdapter'));
            $select = $tableGateway->getSql()->select();
            $select->columns(['label', 'language_id'])
                    ->where(['attribute_id' => $this->getId()]);
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
        try {
            $adapter = $this->getContainer()->get('dbAdapter');
            if (isset($this->storage['label'])) {
                $tableGateway = new TableGateway('eav_attribute_label', $adapter);
                $tableGateway->delete(['attribute_id' => $this->getId()]);
                foreach ($this->storage['label'] as $id => $value) {
                    $tableGateway->insert(['label' => $value, 'attribute_id' => $this->getId(), 'language_id' => $id]);
                }
            }
            if (isset($this->storage['option']) && isset($this->storage['input']) && in_array($this->storage['input'], ['select', 'radio', 'checkbox', 'multiselect'])) {
                $tableGateway = new TableGateway('eav_attribute_option', $adapter);
                $tableGateway->delete(['attribute_id' => $this->getId()]);
                $labelGateway = new TableGateway('eav_attribute_option_label', $adapter);
                $options = [];
                foreach ($this->storage['option'] as $id => $values) {
                    foreach ($values as $key => $value) {
                        if (!isset($options[$key][$id])) {
                            $options[$key][$id] = [];
                        }
                        $options[$key][$id] = $value;
                    }
                }
                $this->storage['option'] = $options;
                foreach ($options as $key => $values) {
                    $tableGateway->insert(['attribute_id' => $this->getId(), 'sort_order' => (isset($this->storage['option-order'][$key]) ? (int) $this->storage['option-order'][$key] : 0)]);
                    $optionId = $tableGateway->getLastInsertValue();
                    foreach ($values as $id => $value) {
                        $labelGateway->insert(['option_id' => $optionId, 'language_id' => $id, 'label' => $value]);
                    }
                }
            }
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

}
