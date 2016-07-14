<?php

namespace Seahinet\Catalog\Indexer;

use Seahinet\Catalog\Model\Collection\Product;
use Seahinet\Catalog\Model\Product as ProductModel;
use Seahinet\Catalog\Model\Collection\Category;
use Seahinet\Lib\Indexer\Handler\AbstractHandler;
use Seahinet\Lib\Indexer\Handler\Database;
use Seahinet\Lib\Indexer\Provider;
use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Sql\Ddl;

class Url implements Provider
{

    use \Seahinet\Lib\Traits\Container;

    protected $path = [];

    public function provideStructure(AbstractHandler $handler)
    {
        if ($handler instanceof Database) {
            $adapter = $this->getContainer()->get('dbAdapter');
            $platform = $adapter->getPlatform();
            $languages = new Language;
            $languages->columns(['id']);
            foreach ($languages as $language) {
                $table = 'catalog_url_' . $language['id'] . '_index';
                $adapter->query(
                        'DROP TABLE IF EXISTS ' . $table, $adapter::QUERY_MODE_EXECUTE
                );
                $ddl = new Ddl\CreateTable($table);
                $ddl->addColumn(new Ddl\Column\Integer('product_id', true, 0))
                        ->addColumn(new Ddl\Column\Integer('category_id', false, 0))
                        ->addColumn(new Ddl\Column\Varchar('path', 255, false))
                        ->addConstraint(new Ddl\Constraint\UniqueKey(['category_id', 'product_id'], 'UNQ_' . strtoupper($table) . '_CATEGORY_ID_PRODUCT_ID'))
                        ->addConstraint(new Ddl\Constraint\ForeignKey('FK_' . strtoupper($table) . '_ID_PRODUCT_ENTITY_ID', 'product_id', 'product_entity', 'id', 'CASCADE', 'CASCADE'))
                        ->addConstraint(new Ddl\Constraint\ForeignKey('FK_' . strtoupper($table) . '_ID_CATEGORY_ENTITY_ID', 'category_id', 'category_entity', 'id', 'CASCADE', 'CASCADE'))
                        ->addConstraint(new Ddl\Index\Index('path', 'IDX_' . strtoupper($table) . '_PATH'));
                $adapter->query(
                        $ddl->getSqlString($platform), $adapter::QUERY_MODE_EXECUTE
                );
            }
        } else {
            $handler->buildStructure([['attr' => 'path', 'is_unique' => 1]]);
        }
        return true;
    }

    public function provideData(AbstractHandler $handler)
    {
        $data = [];
        $languages = new Language;
        $languages->columns(['id']);
        foreach ($languages as $language) {
            $categories = new Category($language['id']);
            $categories->where(['status' => 1]);
            $categories->load(false);
            $data[$language['id']] = [];
            $tree = [];
            foreach ($categories as $category) {
                $tree[$category['id']] = [
                    'object' => $category,
                    'pid' => (int) $category['parent_id']
                ];
            }
            foreach ($categories as $category) {
                if ($path = $this->getPath($category, $tree)) {
                    $data[$language['id']][$category['id']] = [
                        'product_id' => null,
                        'category_id' => $category['id'],
                        'path' => $path
                    ];
                }
            }
            $products = new Product($language['id']);
            $products->where(['status' => 1]);
            $products->load(false);
            foreach ($products as $product) {
                $product = new ProductModel($language['id'], $product);
                $categories = $product->getCategories();
                foreach ($categories as $category) {
                    $data[$language['id']][] = [
                        'product_id' => $product['id'],
                        'category_id' => $category['id'],
                        'path' => (isset($data[$language['id']][$category['id']]['path']) ?
                                ($data[$language['id']][$category['id']]['path'] . '/') : '') .
                        $product['uri_key']
                    ];
                }
            }
            $data[$language['id']] = array_values($data[$language['id']]);
        }
        $handler->buildData($data);
        return true;
    }

    private function getPath($category, $tree)
    {
        if (isset($this->path[$category['id']])) {
            return $this->path[$category['id']];
        }
        if (!isset($category['uri_key'])) {
            return '';
        }
        $path = $category['uri_key'];
        $pid = (int) $category['parent_id'];
        if ($pid && isset($tree[$pid])) {
            $path = trim($this->getPath($tree[$pid]['object'], $tree) . '/' . $path, '/');
        }
        $this->path[$category['id']] = $path;
        return $path;
    }

}
