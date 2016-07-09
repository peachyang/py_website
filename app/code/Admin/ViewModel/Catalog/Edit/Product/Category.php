<?php

namespace Seahinet\Admin\ViewModel\Catalog\Edit\Product;

use Seahinet\Catalog\Model\Collection\Category as Collection;

class Category extends Tab
{

    protected $categories = null;
    protected $activeIds = null;

    public function getCategories()
    {
        if (is_null($this->categories)) {
            $collection = new Collection;
            $collection->order('parent_id ASC, sort_order DESC');
            $this->categories = [];
            foreach ($collection as $item) {
                if (!isset($this->categories[(int) $item['parent_id']])) {
                    $this->categories[(int) $item['parent_id']] = [];
                }
                $this->categories[(int) $item['parent_id']][] = $item;
            }
        }
        return $this->categories;
    }

    public function getActiveIds()
    {
        if (is_null($this->activeIds)) {
            $collection = $this->getProduct()->getCategories();
            $this->activeIds = [];
            if (count($collection)) {
                foreach ($collection->toArray() as $item) {
                    $this->activeIds[] = $item['id'];
                }
            }
        }
        return $this->activeIds;
    }

    public function renderCategory($level = 0)
    {
        $html = '';
        if (!empty($this->getCategories()[$level])) {
            foreach ($this->getCategories()[$level] as $category) {
                $html .= '<li><input type="checkbox" name="category[]" id="category-' .
                        $category['id'] . '" class="form-control" value="' .
                        $category['id'] . '"' . (in_array($category['id'], $this->getActiveIds()) ?
                                ' checked="checked"' : '') . ' /><label for="category-' .
                        $category['id'] . '" class="control-label">' . $category['name'] .
                        '</label>' . (isset($this->getCategories()[$category['id']]) ?
                                '<ul>' . $this->renderCategory($category['id']) . '</ul>' : ''
                        ) . '</li>';
            }
        }
        return $html;
    }

}