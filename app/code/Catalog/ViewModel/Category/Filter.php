<?php

namespace Seahinet\Catalog\ViewModel\Category;

use Seahinet\Catalog\Model\Product;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\Collection\Eav\Attribute;

class Filter extends Toolbar
{

    protected $showCount = false;

    public function showCount($flag = null)
    {
        if (is_bool($flag)) {
            $this->showCount = $flag;
        }
        return $this->showCount;
    }

    public function getCurrentFilters()
    {
        $query = $this->getQuery();
        return array_diff_key($query, ['desc' => 1, 'asc' => 1, 'page' => 1, 'limit' => 1, 'q' => 1]);
    }

    public function getFilters()
    {
        $result = [];
        if ($this->getCollection()->count()) {
            $languageId = Bootstrap::getLanguage()->getId();
            if ($this->getVariable('category')) {
                $ids = [];
                foreach ($this->getVariable('category')->getChildrenCategories() as $category) {
                    $ids[] = $category['id'];
                }
            }
            $attributes = new Attribute;
            $attributes->withLabel()
                    ->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [], 'left')
                    ->where(['filterable' => 1, 'eav_entity_type.code' => Product::ENTITY_TYPE]);
            foreach ($this->getCollection() as $product) {
                if (!empty($ids)) {
                    $product = new Product($languageId, $product);
                    foreach ($product->getCategories() as $category) {
                        if (in_array($category['id'], $ids)) {
                            if (!isset($result['category'])) {
                                $result['category'] = [
                                    'label' => 'Category',
                                    'values' => []
                                ];
                            }
                            if (!isset($result['category']['values'][$category['id']])) {
                                $result['category']['values'][$category['id']] = ['label' => $category['name'], 'count' => 1];
                            } else {
                                $result['category']['values'][$category['id']]['count'] ++;
                            }
                        }
                    }
                }
                foreach ($attributes as $attribute) {
                    if (!isset($result[$attribute['code']])) {
                        $result[$attribute['code']] = [
                            'label' => $attribute['label'],
                            'values' => []
                        ];
                    }
                    if (!isset($result[$attribute['code']]['values'][$product[$attribute['code']]])) {
                        $result[$attribute['code']]['values'][$product[$attribute['code']]] = ['label' => $product[$attribute['code']], 'count' => 1];
                    } else {
                        $result[$attribute['code']]['values'][$product[$attribute['code']]]['count'] ++;
                    }
                }
            }
        }
        return $result;
    }

    public function getFilterUrl($key, $value)
    {
        $query = $this->getCurrentUri()->getQuery();
        $query[$key] = $value;
        return $this->getCurrentUri()->withQuery($query);
    }

    public function getInitFilterUrl($key = null)
    {
        $query = $this->getRequest()->getQuery();
        if (is_null($key)) {
            return $this->getCurrentUri()->withQuery(http_build_query(array_intersect_key($query, ['desc' => 1, 'asc' => 1, 'page' => 1, 'limit' => 1])));
        } else {
            unset($query[$key]);
        }
        return $this->getCurrentUri()->withQuery(http_build_query($query));
    }

}
