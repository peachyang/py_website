<?php

namespace Seahinet\Admin\ViewModel\Catalog\Edit\Product;

use Seahinet\Catalog\Model\Collection\Category as Collection;

class Category extends Tab
{

    protected $categories = null;

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

    public function renderCategory($level = 0)
    {
        $html = '';
        if (!empty($this->getCategories()[$level])) {
            foreach ($this->getCategories()[$level] as $category) {
                $html = '<li><input type="checkbox" name="category[]" id="category-' .
                        $category['id'] . '" class="form-control" value="' .
                        $category['id'] . '" /><label for="category-' .
                        $category['id'] . '" class="control-label">' . $category['name'] .
                        '</label>' . (isset($this->getCategories()[$category['id']]) ?
                                '<ul>' . $this->renderCategory($category['id']) . '</ul>' : ''
                        ) . '</li>';
            }
        }
        return $html;
    }

}
