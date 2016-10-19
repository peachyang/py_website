<?php

namespace Seahinet\Catalog\Model\Product;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\AbstractModel;

class Option extends AbstractModel
{

    protected $languageId;

    protected function construct()
    {
        $this->init('product_option', 'id', ['id', 'product_id', 'input', 'is_required', 'sku', 'price', 'is_fixed', 'sort_order']);
    }

    protected function getLanguageId()
    {
        if (!$this->languageId) {
            $this->languageId = Bootstrap::getLanguage()->getId();
        }
        return $this->languageId;
    }

    public function getLabel($languageId = null)
    {
        if ($this->getId()) {
            $tableGateway = $this->getTableGateway('product_option_title');
            $result = $tableGateway->select([
                        'option_id' => $this->getId(),
                        'language_id' => is_null($languageId) ? $languageId : $this->getLanguageId()
                    ])->toArray();
        }
        return empty($result) ? '' : $result[0]['title'];
    }

    public function getValues()
    {
        if ($this->storage['id']) {
            if (!isset($this->storage['value'])) {
                if (in_array($this->storage['input'], ['select', 'radio', 'checkbox', 'multiselect'])) {
                    $tableGateway = $this->getTableGateway('product_option_value');
                    $select = $tableGateway->getSql()->select();
                    $select->where(['option_id' => $this->getId()]);
                    $select->join('product_option_value_title', 'product_option_value.id=product_option_value_title.value_id', ['title'], 'left')
                            ->where(['product_option_value_title.language_id' => $this->getLanguageId()]);
                    $this->storage['value'] = $tableGateway->selectWith($select)->toArray();
                } else {
                    $this->storage['value'] = [];
                }
            }
            return $this->storage['value'];
        }
        return [];
    }

    public function getValue($value, $titleOnly = true)
    {
        if ($this->storage['id']) {
            if (in_array($this->storage['input'], ['select', 'radio', 'checkbox', 'multiselect'])) {
                $tableGateway = $this->getTableGateway('product_option_value');
                $select = $tableGateway->getSql()->select();
                $select->where(['option_id' => $this->getId(), 'product_option_value.id' => $value]);
                $select->join('product_option_value_title', 'product_option_value.id=product_option_value_title.value_id', ['title'], 'left')
                        ->where(['product_option_value_title.language_id' => $this->getLanguageId()]);
                $result = $tableGateway->selectWith($select)->toArray();
                if ($result) {
                    return $titleOnly ? $result[0]['title'] : $result[0];
                }
            } else {
                return $value;
            }
        }
        return '';
    }

    protected function isUpdate($constraint = array(), $insertForce = false)
    {
        if (!$this->getId()) {
            return false;
        } else if (!$this->isLoaded) {
            $obj = (new static)->load($this->getId());
        } else {
            $obj = $this;
        }
        if ($this->offsetGet('product_id') == $obj->offsetGet('product_id')) {
            return true;
        } else {
            $this->setId(null);
            return false;
        }
    }

    protected function afterSave()
    {
        $languageId = Bootstrap::getLanguage()->getId();
        if ($this->storage['label']) {
            $tableGateway = $this->getTableGateway('product_option_title');
            $this->upsert(['title' => $this->storage['label']], ['option_id' => $this->getId(), 'language_id' => $languageId], $tableGateway);
        }
        if ($this->storage['value']) {
            $tableGateway = $this->getTableGateway('product_option_value');
            $titleGateway = $this->getTableGateway('product_option_value_title');
            foreach ($this->storage['value']['sku'] as $order => $sku) {
                if ($this->storage['value']['label'][$order]) {
                    $tableGateway->insert([
                        'id' => null,
                        'sku' => $sku,
                        'price' => (float) $this->storage['value']['price'][$order],
                        'is_fixed' => $this->storage['value']['is_fixed'][$order],
                        'sort_order' => $order,
                        'option_id' => $this->getId()
                    ]);
                    $valueId = $tableGateway->getLastInsertValue();
                    $this->upsert(['title' => $this->storage['value']['label'][$order]], ['value_id' => $valueId, 'language_id' => $languageId], $titleGateway);
                }
            }
        }
        parent::afterSave();
    }

}
