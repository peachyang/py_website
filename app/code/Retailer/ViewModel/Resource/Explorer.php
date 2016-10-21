<?php

namespace Seahinet\Retailer\ViewModel\Resource;

use Seahinet\Lib\Bootstrap;
use Seahinet\Retailer\ViewModel\AbstractViewModel;
use Seahinet\Resource\Model\Collection\{
    Resource,
    Category
};

class Explorer extends AbstractViewModel
{

    protected static $tree = null;
    protected $type = [
        'application/x-compressed' => 'archive',
        'application/x-compress' => 'archive',
        'application/x-tar' => 'archive',
        'application/x-xz' => 'archive',
        'application/x-rar-compressed' => 'archive',
        'application/x-gtar' => 'archive',
        'application/x-gzip' => 'archive',
        'application/x-bzip2' => 'archive',
        'application/zip' => 'archive',
        'application/pdf' => 'pdf',
        'audio' => 'audio',
        'image' => 'image',
        'video' => 'video',
        'text' => 'text'
    ];

    public function getCurrentUrl()
    {
        $uri = $this->getRequest()->getUri();
        return $uri->withFragment('')->withQuery('');
    }

    public function getLanguageId()
    {
        return Bootstrap::getLanguage()->getId();
    }

    public function getFileTypes()
    {
        $collection = new Resource;
        $collection->columns(['file_type'])
                ->group(['file_type', 'store_id'])
                ->where(['store_id' => $this->getStore()->getId()]);
        return $collection;
    }

    public function getResourceType($type)
    {
        return $this->type[$type] ?? $this->type[substr($type, 0, strpos($type, '/'))] ?? 'file';
    }

    public function getResources()
    {
        $collection = new Resource;
        $collection->where([
            'store_id' => $this->getStore()->getId(),
            'category_id' => $this->getQuery('category_id') ?: null
        ])->limit((int) $this->getQuery('limit', 20));
        return $collection;
    }

    public function getCategoryTree()
    {
        if (is_null(static::$tree)) {
            $collection = new Category;
            $collection->where(['store_id' => $this->getStore()->getId()]);
            static::$tree = [];
            foreach ($collection as $category) {
                if (!isset(static::$tree[(int) $category->offsetGet('parent_id')])) {
                    static::$tree[(int) $category->offsetGet('parent_id')] = [];
                }
                static::$tree[(int) $category->offsetGet('parent_id')][] = $category;
            }
        }
        return static::$tree;
    }

    public function getChildrenCategories($id = 0, $title = null)
    {
        $child = new static;
        $child->setTemplate('retailer/resource/category')
                ->setVariables([
                    'id' => $id,
                    'title' => is_null($title) ? $this->translate('All Categories') :
                            (is_scalar($title) ? $title : ($title[$this->getLanguageId()] ?? current($title)))
        ]);
        return $child;
    }

}
