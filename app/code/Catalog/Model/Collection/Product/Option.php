<?php

namespace Seahinet\Catalog\Model\Collection\Product;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\AbstractCollection;

class Option extends AbstractCollection
{

    protected $languageId;

    protected function construct()
    {
        $this->init('product_option');
    }

    public function withLabel($languageId = null)
    {
        if (is_null($languageId)) {
            $this->languageId = Bootstrap::getLanguage()->getId();
        } else if (is_object($languageId)) {
            $this->languageId = $languageId['id'];
        } else {
            $this->languageId = $languageId;
        }
        $this->select->join('product_option_title', 'product_option_title.option_id=product_option.id', ['title'], 'left')
                ->where(['product_option_title.language_id' => $this->languageId]);
        return $this;
    }

    public function afterLoad(&$result)
    {
        $tableGateway = $this->getTableGateway('product_option_value');
        foreach ($result as &$item) {
            if (in_array($item['input'], ['select', 'radio', 'checkbox', 'multiselect'])) {
                $select = $tableGateway->getSql()->select();
                $select->where(['option_id' => $item['id']]);
                if ($this->languageId) {
                    $select->join('product_option_value_title', 'product_option_value.id=product_option_value_title.value_id', ['title'], 'left')
                            ->where(['product_option_value_title.language_id' => $this->languageId]);
                }
                $item['value'] = $tableGateway->selectWith($select)->toArray();
            } else {
                $item['value'] = [];
            }
        }
        parent::afterLoad($result);
    }

}
