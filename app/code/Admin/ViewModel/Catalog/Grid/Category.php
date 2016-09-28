<?php

namespace Seahinet\Admin\ViewModel\Catalog\Grid;

use Seahinet\Admin\ViewModel\Eav\Grid as PGrid;
use Seahinet\Catalog\Model\Collection\Category as Collection;
use Seahinet\Lib\Session\Segment;

class Category extends PGrid
{

    protected $editUrl = '';
    protected $deleteUrl = '';
    protected $action = ['getAppendAction', 'getEditAction', 'getDeleteAction'];
    protected $translateDomain = 'catalog';
    protected $categoryTree = [];

    public function __clone()
    {
        $this->variables = [];
        $this->children = [];
    }

    public function getEditAction($item)
    {
        return '<a href="' . $this->getEditUrl() . '?id=' . $item['id'] . '&pid=' .
                $item['parent_id'] . '" title="' . $this->translate('Edit') .
                '"><span class="fa fa-fw fa-file-text-o" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Edit') . '</span></a>';
    }

    public function getDeleteAction($item)
    {
        return '<a href="' . $this->getDeleteUrl() . '" data-method="delete" data-params="id=' . $item['id'] .
                '&csrf=' . $this->getCsrfKey() . '" title="' . $this->translate('Delete') .
                '"><span class="fa fa-fw fa-remove" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Delete') . '</span></a>';
    }

    public function getAppendAction($item)
    {
        return '<a href="' . $this->getEditUrl() . '?pid=' . $item['id'] . '" title="' . $this->translate('Append Subcategory') .
                '"><span class="fa fa-fw fa-plus" aria-hidden="true"></span><span class="sr-only">' .
                $this->translate('Append') . '</span></a>';
    }

    public function getEditUrl()
    {
        if ($this->editUrl === '') {
            $this->editUrl = $this->getAdminUrl(':ADMIN/catalog_category/edit/');
        }
        return $this->editUrl;
    }

    public function getDeleteUrl()
    {
        if ($this->deleteUrl === '') {
            $this->deleteUrl = $this->getAdminUrl(':ADMIN/catalog_category/delete/');
        }
        return $this->deleteUrl;
    }

    protected function prepareColumns($columns = [])
    {
        return parent::prepareColumns([
                    'id' => [
                        'label' => 'ID',
                    ]
        ]);
    }

    protected function prepareCollection($collection = null)
    {
        $collection = new Collection;
        $user = (new Segment('admin'))->get('user');
        if ($user->getStore()) {
            $collection->where(['store_id' => $user->getStore()->getId()]);
        }
        return $collection;
    }

    protected function prepareCategoryTree()
    {
        $collection = $this->getVariable('collection');
        if ($collection->count()) {
            foreach ($collection as $category) {
                if (!isset($this->categoryTree[(int) $category['parent_id']])) {
                    $this->categoryTree[(int) $category['parent_id']] = [];
                }
                $this->categoryTree[(int) $category['parent_id']][] = $category;
            }
            foreach ($this->categoryTree as $key => $value) {
                uasort($this->categoryTree[$key], function($a, $b) {
                    if (!isset($a['sort_order'])) {
                        $a['sort_order'] = 0;
                    }
                    if (!isset($b['sort_order'])) {
                        $b['sort_order'] = 0;
                    }
                    return $a['sort_order'] <=> $b['sort_order'];
                });
            }
        }
    }

    public function getChildrenCategories($pid)
    {
        if (empty($this->categoryTree)) {
            $this->prepareCategoryTree();
        }
        return $this->categoryTree[$pid] ?? [];
    }

    public function renderCategory($category, $level = 1)
    {
        $child = clone $this;
        $child->setTemplate('admin/catalog/category/renderer')
                ->setVariable('category', $category)
                ->setVariable('children', $this->getChildrenCategories($category['id']))
                ->setVariable('level', $level);
        return $child;
    }

}
