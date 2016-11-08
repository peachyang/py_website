<?php

namespace Seahinet\Catalog\Indexer;

use Seahinet\Catalog\Model\Collection\Product as Collection;
use Seahinet\Lib\Db\Sql\Ddl\Column\UnsignedInteger;
use Seahinet\Lib\Indexer\Handler\AbstractHandler;
use Seahinet\Lib\Indexer\Handler\Database;
use Seahinet\Lib\Indexer\Provider;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Sql\Ddl;

class Search implements Provider
{

    use \Seahinet\Lib\Traits\Container;

    public function provideStructure(AbstractHandler $handler)
    {
        if ($handler instanceof Database) {
            $adapter = $this->getContainer()->get('dbAdapter');
            $platform = $adapter->getPlatform();
            $languages = new Language;
            $languages->columns(['id']);
            foreach ($languages as $language) {
                $table = 'catalog_search_' . $language['id'] . '_index';
                $adapter->query(
                        'DROP TABLE IF EXISTS ' . $table, $adapter::QUERY_MODE_EXECUTE
                );
                if ($platform->getName() === 'MySQL') {
                    $adapter->query(
                            'CREATE TABLE `' . $table . '`(`id` INTEGER UNSIGNED NOT NULL,`store_id` INTEGER UNSIGNED NOT NULL,`data` LONGTEXT,PRIMARY KEY (`id`),INDEX IDX_CATALOG_SEARCH_1_INDEX_STORE_ID (`store_id`),CONSTRAINT FK_' .
                            strtoupper($table) . '_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,CONSTRAINT FK_' .
                            strtoupper($table) . '_ID_PRODUCT_ENTITY_ID FOREIGN KEY (`id`) REFERENCES `product_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,FULLTEXT INDEX `FTI_' .
                            strtoupper($table) . '_FULLTEXT_DATA` (`data`));', $adapter::QUERY_MODE_EXECUTE
                    );
                } else {
                    $ddl = new Ddl\CreateTable($table);
                    $ddl->addColumn(new UnsignedInteger('id', false, 0))
                            ->addColumn(new UnsignedInteger('store_id', false, 1))
                            ->addColumn(new Ddl\Column\Text('data', 2147483648, true))
                            ->addConstraint(new Ddl\Constraint\PrimaryKey('id'))
                            ->addConstraint(new Ddl\Constraint\ForeignKey('FK_' . strtoupper($table) . '_STORE_ID_CORE_STORE_ID', 'store_id', 'core_store', 'id', 'CASCADE', 'CASCADE'))
                            ->addConstraint(new Ddl\Constraint\ForeignKey('FK_' . strtoupper($table) . '_ID_PRODUCT_ENTITY_ID', 'id', 'product_entity', 'id', 'CASCADE', 'CASCADE'))
                            ->addConstraint(new Ddl\Index\Index('data', 'IDX_' . strtoupper($table) . '_DATA', [2147483648]));
                    $adapter->query(
                            $ddl->getSqlString($platform), $adapter::QUERY_MODE_EXECUTE
                    );
                }
            }
        } else {
            $handler->buildStructure([['attr' => 'data', 'fulltext' => 1]]);
        }
        return true;
    }

    public function provideData(AbstractHandler $handler)
    {
        $attributes = new Attribute;
        $attributes->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [], 'left')
                ->where(['eav_entity_type.code' => Collection::ENTITY_TYPE, 'searchable' => 1]);
        $data = [];
        $languages = new Language;
        $languages->columns(['id']);
        foreach ($languages as $language) {
            $data[$language['id']] = [];
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
                        'data' => $text
                    ];
                }
            }
            $handler->buildData($data);
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
