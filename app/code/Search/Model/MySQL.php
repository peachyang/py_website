<?php

namespace Seahinet\Search\Model;

use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Sql\Expression;

class MySQL implements EngineInterface
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Lib\Traits\DB;

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
                    'CREATE TABLE `' . $table . '`(`id` INTEGER UNSIGNED NOT NULL,`store_id` INTEGER UNSIGNED NOT NULL,`data` LONGTEXT,PRIMARY KEY (`id`),INDEX IDX_CATALOG_SEARCH_1_INDEX_STORE_ID (`store_id`),FULLTEXT INDEX `FTI_' .
                    strtoupper($table) . '_FULLTEXT_DATA` (`data`) WITH PARSER ngram);', $adapter::QUERY_MODE_EXECUTE
            );
        }
    }

    public function select($prefix, $data, $languageId)
    {
        $config = $this->getContainer()->get('config');
        $limit = (int) ($data['limit'] ?? empty($data['mode']) ?
                $config['catalog/frontend/default_per_page_grid'] :
                $config['catalog/frontend/default_per_page_' . $data['mode']]);
        $key = md5($prefix . $languageId . $data['q'] . $limit . ($data['page'] ?? 1));
        $cache = $this->getContainer()->get('cache');
        $result = $cache->fetch($key, 'INDEX_');
        if (empty($result) && !is_array($result)) {
            $tableGateway = $this->getTableGateway($prefix . '_' . $languageId . '_index');
            $select = $tableGateway->getSql()->select();
            $select->columns(['id', 'weight' => new Expression('MATCH(data) AGAINST(\'' . $data['q'] . '\')')])
                    ->where('MATCH(data) AGAINST(\'' . $data['q'] . '\')');
            if (!empty($data['store_id'])) {
                $select->where(['store_id' => $data['store_id']]);
            }
            $select->limit($limit);
            if (isset($data['page'])) {
                $select->offset((int) ($data['page'] - 1) * $limit);
            }
            $result = $tableGateway->selectWith($select)->toArray();
            $cache->save($key, $result, 'INDEX_');
        }
        return $result;
    }

    public function delete($prefix, $id, $languageId)
    {
        $tableGateway = $this->getTableGateway($prefix . '_' . $languageId . '_index');
        $tableGateway->delete(['id' => $id]);
    }

    public function update($prefix, $data)
    {
        foreach ($data as $languageId => $collection) {
            $tableGateway = $this->getTableGateway($prefix . '_' . $languageId . '_index');
            foreach ($collection as $item) {
                $tableGateway->insert($item);
            }
        }
        $this->getContainer()->get('cache')->delete('', 'INDEX_');
    }

}
