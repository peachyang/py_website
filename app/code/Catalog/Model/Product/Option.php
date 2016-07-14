<?php

namespace Seahinet\Catalog\Model\Product;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\AbstractModel;
use Zend\Db\TableGateway\TableGateway;

class Option extends AbstractModel
{

    protected function construct()
    {
        $this->init('product_option', 'id', ['id', 'product_id', 'input', 'is_required', 'sku', 'price', 'is_fixed', 'sort_order']);
    }

    protected function isUpdate($constraint = array(), $insertForce = false)
    {
        if (!$this->isLoaded && $this->getId()) {
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
        $adapter = $this->getContainer()->get('dbAdapter');
        $languageId = Bootstrap::getLanguage()->getId();
        if ($this->storage['label']) {
            $tableGateway = new TableGateway('product_option_title', $adapter);
            $this->upsert(['title' => $this->storage['label']], ['option_id' => $this->getId(), 'language_id' => $languageId], $tableGateway);
        }
        if ($this->storage['value']) {
            $tableGateway = new TableGateway('product_option_value', $adapter);
            $titleGateway = new TableGateway('product_option_value_title', $adapter);
            foreach ($this->storage['value']['sku'] as $order => $sku) {
                if ($this->storage['value']['label'][$order]) {
                    if ($this->storage['value']['id'][$order]) {
                        $tableGateway->update([
                            'sku' => $sku,
                            'price' => $this->storage['value']['price'][$order],
                            'is_fixed' => $this->storage['value']['is_fixed'][$order],
                            'sort_order' => $order,
                            'option_id' => $this->getId()
                                ], ['id' => $this->storage['value']['id'][$order]]);
                        $valueId = $this->storage['value']['id'][$order];
                    } else {
                        $tableGateway->insert([
                            'sku' => $sku,
                            'price' => $this->storage['value']['price'][$order],
                            'is_fixed' => $this->storage['value']['is_fixed'][$order],
                            'sort_order' => $order,
                            'option_id' => $this->getId()
                        ]);
                        $valueId = $tableGateway->getLastInsertValue();
                    }
                    $this->upsert(['title' => $this->storage['value']['label'][$order]], ['value_id' => $valueId, 'language_id' => $languageId], $titleGateway);
                }
            }
        }
        parent::afterSave();
    }

}
