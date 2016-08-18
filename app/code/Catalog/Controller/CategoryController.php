<?php

namespace Seahinet\Catalog\Controller;

use Seahinet\Catalog\Model\Category;
use Seahinet\Lib\Model\Eav\Attribute;
use Seahinet\Lib\Controller\ActionController;
use Zend\Db\Sql\Predicate\Like;

class CategoryController extends ActionController
{

    use \Seahinet\Catalog\Traits\Breadcrumb;

    public function indexAction()
    {
        if ($this->getOption('category_id')) {
            $category = new Category;
            $category->load($this->getOption('category_id'));
            $root = $this->getLayout('catalog_category');
            $root->getChild('head')->setTitle($category['meta_title']? : $category['name'])
                    ->setDescription($category['meta_description'])
                    ->setKeywords($category['meta_keywords']);
            $content = $root->getChild('content');
            $this->generateCrumbs($content->getChild('breadcrumb'), $this->getOption('category_id'));
            $products = $this->prepareCollection($category->getProducts(), $category);
            $content->getChild('toolbar')->setCategory($category)->setCollection($products);
            $content->getChild('list')->setCategory($category)->setProducts($products);
            $content->getChild('toolbar_bottom')->setCategory($category)->setCollection($products);
            return $root;
        }
        return $this->notFoundAction();
    }

    protected function prepareCollection($collection, $category = null)
    {
        $condition = $this->getRequest()->getQuery();
        $config = $this->getContainer()->get('config');
        $mode = isset($condition['mode']) ? $condition['mode'] : 'grid';
        unset($condition['q'], $condition['type'], $condition['mode']);
        if ($condition['limit'] === 'all' && $config['catalog/frontend/allowed_all_products']) {
            $collection->reset('limit')->reset('offset');
        } else {
            $limit = isset($condition['limit']) && in_array($condition['limit'], explode(',', trim($config['catalog/frontend/allowed_per_page_' . $mode], ','))) ?
                    $condition['limit'] : $config['catalog/frontend/default_per_page_' . $mode];
            if (isset($condition['page'])) {
                $collection->offset(($condition['page'] - 1) * $limit);
                unset($condition['page']);
            }
            $collection->limit((int) $limit);
        }
        unset($condition['limit']);
        if (isset($condition['asc'])) {
            $collection->order((strpos($condition['asc'], ':') ?
                            str_replace(':', '.', $condition['asc']) :
                            $condition['asc']) . ' ASC');
            unset($condition['asc'], $condition['desc']);
        } else if (isset($condition['desc'])) {
            $collection->order((strpos($condition['desc'], ':') ?
                            str_replace(':', '.', $condition['desc']) :
                            $condition['desc']) . ' DESC');
            unset($condition['desc']);
        } else if ($category && $default = $category['default_sortable']) {
            $collection->order($default);
        }
        if (!empty($condition)) {
            foreach ($condition as $key => $value) {
                if (trim($value) === '') {
                    unset($condition[$key]);
                } else if (strpos($key, ':')) {
                    if (strpos($value, '%') !== false) {
                        $collection->where(new Like(str_replace(':', '.', $key), $value));
                    } else {
                        $condition[str_replace(':', '.', $key)] = $value;
                    }
                    unset($condition[$key]);
                } else if (strpos($value, '%') !== false) {
                    $collection->where(new Like($key, $value));
                    unset($condition[$key]);
                } else {
                    $attribute = new Attribute;
                    $attribute->load($key, 'code');
                    if (in_array($attribute->offsetGet('input'), ['checkbox', 'multiselect'])) {
                        $collection->where('FIND_IN_SET("' . $value . '",' . $key . ')');
                        unset($condition[$key]);
                    }
                }
            }
            $collection->where($condition);
        }
        return $collection;
    }

}
