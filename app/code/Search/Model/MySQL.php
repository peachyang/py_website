<?php

namespace Seahinet\Search\Model;

use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Expression;

class MySQL implements EngineInterface
{

    use \Seahinet\Lib\Traits\Container;

    public function select($prefix, $data, $languageId)
    {
        $config = $this->getContainer()->get('config');
        $where = new Where;
        if (!empty($data['store_id'])) {
            $where->equalTo('store_id', $data['store_id']);
        }
        $where->predicate(new Expression('match(data) against(\'' . $data['q'] . '\')>0.001'));
        $options = [];
        $options['limit'] = (int) ($data['limit'] ?? empty($data['mode']) ?
                $config['catalog/frontend/default_per_page_grid'] :
                $config['catalog/frontend/default_per_page_' . $data['mode']]);
        if (isset($data['page'])) {
            $options['offset'] = (int) ($data['page'] - 1) * $options['limit'];
        }
        return $this->getContainer()->get('indexer')->select($prefix, $languageId, $where, $options);
    }

    public function update($prefix, $data)
    {
        $indexer = $this->getContainer()->get('indexer');
        foreach ($data as $languageId => $collection) {
            foreach ($collection as $item) {
                $indexer->insert($prefix, $languageId, $item);
            }
        }
    }

    public function createIndex($prefix)
    {
        $adapter = $this->getContainer()->get('dbAdapter');
        $languages = new Language;
        $languages->columns(['id']);
        foreach ($languages as $language) {
            $table = $prefix . '_' . $language['id'] . '_index';
            $adapter->query(
                    'DROP TABLE IF EXISTS ' . $table, $adapter::QUERY_MODE_EXECUTE
            );
            $adapter->query(
                    'CREATE TABLE `' . $table . '`(`id` INTEGER UNSIGNED NOT NULL,`store_id` INTEGER UNSIGNED NOT NULL,`data` LONGTEXT,PRIMARY KEY (`id`),INDEX IDX_CATALOG_SEARCH_1_INDEX_STORE_ID (`store_id`),CONSTRAINT FK_' .
                    strtoupper($table) . '_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,CONSTRAINT FK_' .
                    strtoupper($table) . '_ID_PRODUCT_ENTITY_ID FOREIGN KEY (`id`) REFERENCES `product_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,FULLTEXT INDEX `FTI_' .
                    strtoupper($table) . '_FULLTEXT_DATA` (`data`));', $adapter::QUERY_MODE_EXECUTE
            );
        }
    }

}
