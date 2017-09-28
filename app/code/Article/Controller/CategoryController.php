<?php

namespace Seahinet\Article\Controller;

use Seahinet\Article\Model\Category;
use Seahinet\Article\Model\Collection\Category as Collection;
use Seahinet\Api\Model\Collection\Rest\Attribute;
use Seahinet\Lib\Controller\ActionController;
use Zend\Db\Sql\Expression;

class CategoryController extends ActionController
{

    use \Seahinet\Article\Traits\Breadcrumb,
        \Seahinet\Lib\Traits\DB,
        \Seahinet\Lib\Traits\Filter;

    public function indexAction()
    {
        if ($this->getOption('category_id')) {
            $category = new Category;
            $category->load($this->getOption('category_id'));
            $products = $this->prepareCollection($category->getProducts(), $category);
            if ($this->getOption('is_json')) {
                $result = [];
                $columns = new Attribute;
                $columns->columns(['attributes'])
                        ->where([
                            'role_id' => 0,
                            'operation' => 1,
                            'resource' => $products::ENTITY_TYPE
                ]);
                $columns->load(true, true);
                if (count($columns)) {
                    $columns = explode(',', $columns[0]['attributes']);
                    $columns[] = 'id';
                    $products->columns($columns);
                    $products->walk(function($item) use (&$result, $category) {
                        $result[] = [
                            'absolute_url' => $item->getUrl($category),
                            'thumbnail_url' => $item->getThumbnail()
                                ] + $item->toArray();
                    });
                }
                return $result;
            } else {
                $root = $this->getLayout('article_category');
                $root->getChild('head')->setTitle($category['meta_title'] ?: $category['name'])
                        ->setDescription($category['meta_description'])
                        ->setKeywords($category['meta_keywords']);
                $content = $root->getChild('content');
                $this->generateCrumbs($content->getChild('breadcrumb'), $this->getOption('category_id'));
                $content->getChild('toolbar')->setCategory($category)->setCollection($products);
                $content->getChild('list')->setCategory($category)->setProducts($products);
                $content->getChild('toolbar_bottom')->setCategory($category)->setCollection($products);
                return $root;
            }
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
            $tableGateway = $this->getTableGateway('article_in_category');
            $tmpselect = $tableGateway->getSql()->select();
            $tmpselect->columns(['article_id', 'count' => new Expression('count(category_id)')])
                    ->where(['category_id' => $condition['category']], 'OR')
                    ->where(['category_id' => $category['id']], 'OR')
                    ->group(['article_id'])
                    ->having('count>1');
            $set = $tableGateway->selectWith($tmpselect);
            $ids = [];
            foreach ($set as $row) {
                $ids[$row['article_id']] = 1;
            }
            $select->where->in('id', array_keys($ids));
            unset($condition['category']);
        }
        if (isset($condition['limit']) && $condition['limit'] === 'all' && $config['article/frontend/allowed_all_products']) {
            $select->reset('limit')->reset('offset');
        } else {
            $limit = isset($condition['limit']) && in_array($condition['limit'], explode(',', trim($config['article/frontend/allowed_per_page_' . $mode], ','))) ?
                    $condition['limit'] : $config['article/frontend/default_per_page_' . $mode];
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

    public function navAction()
    {
        if ($this->getOption('is_json')) {
            $result = [];
            $columns = new Attribute;
            $columns->columns(['attributes'])
                    ->where([
                        'role_id' => 0,
                        'operation' => 1,
                        'resource' => Collection::ENTITY_TYPE
            ]);
            $columns->load(true, true);
            if (count($columns)) {
                $columns = explode(',', $columns[0]['attributes']);
                $collection = new Collection;
                $collection->columns(array_merge(['id', 'parent_id', 'sort_order'], $columns))
                        ->where(['include_in_menu' => 1, 'parent_id' => null], 'OR')
                        ->order('sort_order ASC');
                $tree = [];
                $collection->walk(function($item) use (&$tree) {
                    if (!isset($tree[(int) $item['parent_id']])) {
                        $tree[(int) $item['parent_id']] = [];
                    }
                    $tree[(int) $item['parent_id']][] = $item;
                });
                if (isset($tree[0])) {
                    $result = $this->generateTree(0, $tree);
                }
            }
            return $result;
        }
        return $this->notFoundAction();
    }

    protected function generateTree($pid, $tree)
    {
        $children = [];
        foreach ($tree[$pid] as $child) {
            if (isset($tree[$child['id']])) {
                $child['children_categories'] = $this->generateTree($child['id'], $tree);
            }
            $array = $child->toArray();
            if (!empty($array['image'])) {
                $array['image'] = $child->getImage();
            }
            if (!empty($array['thumbnail'])) {
                $array['thumbnail'] = $child->getThumbnail();
            }
            $children[] = $array;
        }
        return $children;
    }

}
