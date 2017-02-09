<?php

namespace Seahinet\Search\Model;

use Seahinet\Lib\Db\Sql\Ddl\Column\UnsignedInteger;
use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Sql\Ddl;
use Zend\Db\Sql\Where;

class NoEngine implements EngineInterface
{

    use \Seahinet\Lib\Traits\Container;

    public function createIndex($prefix)
    {
        $adapter = $this->getContainer()->get('dbAdapter');
        $platform = $adapter->getPlatform();
        $languages = new Language;
        $languages->columns(['id']);
        foreach ($languages as $language) {
            $table = $prefix . '_' . $language['id'] . '_index';
            $adapter->query(
                    'DROP TABLE IF EXISTS ' . $table, $adapter::QUERY_MODE_EXECUTE
            );
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

    public function update($prefix, $data)
    {
        $indexer = $this->getContainer()->get('indexer');
        foreach ($data as $languageId => $collection) {
            foreach ($collection as $item) {
                $indexer->insert($prefix, $languageId, $item);
            }
        }
    }

    public function select($prefix, $data, $languageId)
    {
        $config = $this->getContainer()->get('config');
        $where = new Where;
        if (!empty($data['store_id'])) {
            $where->equalTo('store_id', $data['store_id']);
        }
        foreach (explode(' ', $data['q']) as $query) {
            $where->like('data', '%' . $query . '%');
        }
        $options = [];
        $options['limit'] = (int) ($data['limit'] ?? empty($data['mode']) ?
                $config['catalog/frontend/default_per_page_grid'] :
                $config['catalog/frontend/default_per_page_' . $data['mode']]);
        if (isset($data['page'])) {
            $options['offset'] = (int) ($data['page'] - 1) * $options['limit'];
        }
        return $this->getContainer()->get('indexer')->select($prefix, $languageId, $where, $options);
    }

}
