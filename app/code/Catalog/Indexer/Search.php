<?php

namespace Seahinet\Catalog\Indexer;

use Seahinet\Catalog\Model\Collection\Product as Collection;
use Seahinet\Lib\Indexer\Handler\AbstractHandler;
use Seahinet\Lib\Indexer\Handler\Database;
use Seahinet\Lib\Indexer\Provider;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\Model\Collection\Language;

class Search implements Provider
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Search\Traits\Engine;

    protected $engine;

    public function provideStructure(AbstractHandler $handler)
    {
        if ($handler instanceof Database) {
            $this->engine = $this->getSearchEngineHandler();
            $this->engine->createIndex('catalog_search');
        } else {
            $handler->buildStructure([]);
            $this->engine = $this->getSearchEngineHandler('MongoDB');
            $this->engine->createIndex('catalog_search');
        }
        return true;
    }

    public function provideData(AbstractHandler $handler)
    {
        $attributes = new Attribute;
        $attributes->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [], 'left')
                ->where(['eav_entity_type.code' => Collection::ENTITY_TYPE, 'searchable' => 1]);
        $languages = new Language;
        $languages->columns(['id']);
        foreach ($languages as $language) {
            $data = [$language['id'] => []];
            $collection = new Collection($language['id']);
            $collection->where(['status' => 1])->limit(50);
            for ($i = 0;; $i++) {
                $collection->reset('offset')->offset(50 * $i);
                $collection->load(false);
                if (!$collection->count()) {
                    break;
                }
                foreach ($collection as $product) {
                    $text = '|';
                    foreach ($attributes as $attribute) {
                        $text .= $this->getOption($product, $attribute['code'], in_array($attribute['input'], ['select', 'radio', 'checkbox', 'multiselect']) ? $attribute : false);
                    }
                    $data[$language['id']][] = [
                        'id' => $product['id'],
                        'store_id' => $product['store_id'],
                        'data' => preg_replace('/\|{2,}/', '|', $text)
                    ];
                }
            }
            $this->engine->update('catalog_search', $data);
        }
        return true;
    }

    private function getOption($product, $code, $attribute = false)
    {
        $text = '';
        if (is_array($product[$code])) {
            foreach ($product[$code] as $value) {
                $text .= ($attribute ? $attribute->getOption($value) : $value ) . '|';
            }
        } else {
            $text .= ($attribute ? $attribute->getOption($product[$code]) : $product[$code]) . '|';
        }
        return $text;
    }

}
