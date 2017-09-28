<?php

namespace Seahinet\Cms\Controller;

use Seahinet\Cms\Model\Category;
use Seahinet\Cms\Model\Collection\Category as Collection;
use Seahinet\Lib\Controller\ActionController;
use Zend\Db\Sql\Expression;

class CategoryController extends \Seahinet\Lib\Controller\ActionController
{

    use \Seahinet\Catalog\Traits\Breadcrumb,
        \Seahinet\Lib\Traits\DB,
        \Seahinet\Lib\Traits\Filter;

    public function indexAction()
    {
        if ($this->getOption('category_id')) {
            $category = new Category;
            $category->load($this->getOption('category_id'));
            $pages = $this->prepareCollection($category->getPages(), $category);
            $root = $this->getLayout('cms_category');
            $root->getChild('head')->setTitle($category['meta_title'] ?: $category['name'])
                    ->setDescription($category['meta_description'])
                    ->setKeywords($category['meta_keywords']);
            $content = $root->getChild('content');
            $this->generateCrumbs($content->getChild('breadcrumb'), $this->getOption('category_id'));
            $content->getChild('toolbar')->setCategory($category)->setCollection($pages);
            $content->getChild('list')->setCategory($category)->setPages($pages);
            $content->getChild('toolbar_bottom')->setCategory($category)->setCollection($pages);
            return $root;
        }
        return $this->notFoundAction();
    }

    protected function prepareCollection($collection, $category = null)
    {
        if (!is_callable([$collection, 'getSelect'])) {
            return $collection;
        }
        $condition = $this->getRequest()->getQuery();
        $config = $this->getContainer()->get('config');
        $mode = $condition['mode'] ?? 'grid';
        unset($condition['q'], $condition['type'], $condition['mode']);
        $select = $collection->getSelect();
        if ($category && isset($condition['category'])) {
            $tableGateway = $this->getTableGateway('page_in_category');
            $tmpselect = $tableGateway->getSql()->select();
            $tmpselect->columns(['page_id', 'count' => new Expression('count(category_id)')])
                    ->where(['category_id' => $condition['category']], 'OR')
                    ->where(['category_id' => $category['id']], 'OR')
                    ->group(['page_id'])
                    ->having('count>1');
            $set = $tableGateway->selectWith($tmpselect);
            $ids = [];
            foreach ($set as $row) {
                $ids[$row['page_id']] = 1;
            }
            $select->where->in('id', array_keys($ids));
            unset($condition['category']);
        }
        if (isset($condition['limit']) && $condition['limit'] === 'all' && $config['cms/frontend/allowed_all_pages']) {
            $select->reset('limit')->reset('offset');
        } else {
            $limit = isset($condition['limit']) && in_array($condition['limit'], explode(',', trim($config['cms/frontend/allowed_per_page_' . $mode], ','))) ?
                    $condition['limit'] : $config['cms/frontend/default_per_page_' . $mode];
            if (isset($condition['page'])) {
                $select->offset(($condition['page'] - 1) * $limit);
                unset($condition['page']);
            }
            $select->limit((int) $limit);
        }
        unset($condition['limit']);
        if (isset($condition['asc'])) {
            $select->order((strpos($condition['asc'], ':') ?
                            str_replace(':', '.', $condition['asc']) :
                            $condition['asc']) . ' ASC');
            unset($condition['asc'], $condition['desc']);
        } else if (isset($condition['desc'])) {
            $select->order((strpos($condition['desc'], ':') ?
                            str_replace(':', '.', $condition['desc']) :
                            $condition['desc']) . ' DESC');
            unset($condition['desc']);
        } else if ($category && $default = $category['default_sortable']) {
            $select->order($default);
        }
        $this->filter($collection, $condition, ['limit' => 1, 'order' => 1], function($select, &$condition) {
            $condition['status'] = 1;
        });
        return $collection;
    }

}
