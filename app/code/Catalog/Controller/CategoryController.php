<?php

namespace Seahinet\Catalog\Controller;

use Seahinet\Catalog\Model\Category;
use Seahinet\Lib\Model\Eav\Attribute;
use Seahinet\Lib\Controller\ActionController;

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
        unset($condition['q']);
        unset($condition['mode']);
        $limit = isset($condition['limit']) ? $condition['limit'] : 20;
        if (isset($condition['page'])) {
            $collection->offset(($condition['page'] - 1) * $limit);
            unset($condition['page']);
        }
        $collection->limit((int) $limit);
        unset($condition['limit']);
        if (isset($condition['asc'])) {
            $collection->order((strpos($condition['asc'], ':') ?
                            str_replace(':', '.', $condition['asc']) :
                            $condition['asc']) . ' ASC');
            unset($condition['asc']);
        } else if (isset($condition['desc'])) {
            $collection->order((strpos($condition['desc'], ':') ?
                            str_replace(':', '.', $condition['desc']) :
                            $condition['desc']) . ' DESC');
            unset($condition['desc']);
        } else if ($category && $category['default_sortable']) {
            $attribute = new Attribute;
            $attribute->load($category['default_sortable']);
            $collection->order($attribute['code']);
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
                }
            }
            $collection->where($condition);
        }
        return $collection;
    }

}
