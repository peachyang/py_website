<?php

namespace Seahinet\Catalog\Indexer;

use Seahinet\Catalog\Model\Collection\Product as Collection;
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
                            'CREATE TABLE `' . $table . '`(`id` INTEGER NOT NULL,`store_id` INTEGER NOT NULL,`data` LONGTEXT,PRIMARY KEY (`id`),CONSTRAINT FK_' .
                            strtoupper($table) . '_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,CONSTRAINT FK_' .
                            strtoupper($table) . '_ID_PRODUCT_ENTITY_ID FOREIGN KEY (`id`) REFERENCES `prouct_entity`(`id`),FULLTEXT INDEX `FTI_' .
                            strtoupper($table) . '_FULLTEXT_DATA` (`data`)) ENGINE=MyISAM;', $adapter::QUERY_MODE_EXECUTE
                    );
                } else {
                    $ddl = new Ddl\CreateTable($table);
                    $ddl->addColumn(new Ddl\Column\Integer('id', false, 0))
                            ->addColumn(new Ddl\Column\Integer('store_id', false, 1))
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
            $collection = new Collection($language['id']);
            $collection->where(['status' => 1]);
            $collection->load(false);
            $data[$language['id']] = [];
            foreach ($collection as $product) {
                $text = '|';
                foreach ($attributes as $attribute) {
                    $text .= $product[$attribute['code']] . '|';
                }
                $data[$language['id']][] = [
                    'id' => $product['id'],
                    'store_id' => $product['store_id'],
                    'data' => $text
                ];
            }
        }
        $handler->buildData($data);
        return true;
    }

}
