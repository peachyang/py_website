<?php

namespace Seahinet\Cms\Indexer;

use Seahinet\Cms\Model\Collection\Category;
use Seahinet\Cms\Model\Collection\Page;
use Seahinet\Cms\Model\Page as PageModel;
use Seahinet\Lib\Db\Sql\Ddl\Column\UnsignedInteger;
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
                $table = 'cms_url_' . $language['id'] . '_index';
                $adapter->query(
                        'DROP TABLE IF EXISTS ' . $table, $adapter::QUERY_MODE_EXECUTE
                );
                $ddl = new Ddl\CreateTable($table);
                $ddl->addColumn(new UnsignedInteger('page_id', true, 0))
                        ->addColumn(new UnsignedInteger('category_id', true, 0))
                        ->addColumn(new Ddl\Column\Varchar('path', 255, false))
                        ->addConstraint(new Ddl\Constraint\UniqueKey(['category_id', 'page_id'], 'UNQ_' . strtoupper($table) . '_CATEGORY_ID_PAGE_ID'))
                        ->addConstraint(new Ddl\Constraint\ForeignKey('FK_' . strtoupper($table) . '_ID_CMS_PAGE_ID', 'page_id', 'cms_page', 'id', 'CASCADE', 'CASCADE'))
                        ->addConstraint(new Ddl\Constraint\ForeignKey('FK_' . strtoupper($table) . '_ID_CMS_CATEGORY_ID', 'category_id', 'cms_category', 'id', 'CASCADE', 'CASCADE'))
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
        $languages = new Language;
        $languages->columns(['id']);
        foreach ($languages as $language) {
            $data = [$language['id'] => []];
            $tree = [];
            $categories = new Category($language['id']);
            $categories->where(['status' => 1]);
            $categories->load(false);
            foreach ($categories as $category) {
                $tree[$category['id']] = [
                    'object' => $category,
                    'pid' => (int) ($category['parent_id'] ?? 0)
                ];
            }
            foreach ($categories as $category) {
                if ($path = $this->getPath($category, $tree)) {
                    $data[$language['id']][$category['id']] = [
                        'page_id' => null,
                        'category_id' => $category['id'],
                        'path' => $this->getPath($category, $tree)
                    ];
                }
            }
            $handler->buildData($data);
            $pages = new Page;
            $pages->where(['status' => 1])->limit(50);
            $init = $data;
            for ($i = 0;; $i++) {
                $data = [$language['id'] => []];
                $pages->reset('offset')->offset(50 * $i);
                $pages->load(false);
                if (!$pages->count()) {
                    break;
                }
                foreach ($pages as $page) {
                    $page = new PageModel($page);
                    $categories = $page['category'];
                    if (empty($categories)) {
                        $data[$language['id']][] = [
                            'page_id' => $page['id'],
                            'category_id' => null,
                            'path' => $page['uri_key']
                        ];
                    } else {
                        foreach ($categories as $category => $name) {
                            $data[$language['id']][] = [
                                'page_id' => $page['id'],
                                'category_id' => $category,
                                'path' => $init[$language['id']][$category]['path'] . '/' . $page['uri_key']
                            ];
                        }
                    }
                }
                $data[$language['id']] = array_values($data[$language['id']]);
                $handler->buildData($data);
            }
        }
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
