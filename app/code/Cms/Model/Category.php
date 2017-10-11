<?php

namespace Seahinet\Cms\Model;

use Seahinet\Cms\Model\Collection\Category as Collection;
use Seahinet\Cms\Model\Collection\Page as PageCollection;
use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Resource\Model\Resource;
use Zend\Db\Sql\Predicate\In;

class Category extends AbstractModel
{

    protected function construct()
    {
        $this->init('cms_category', 'id', ['id', 'uri_key', 'show_navigation', 'status', 'parent_id']);
    }

    public function getParentCategory()
    {
        if (!empty($this->storage['parent_id'])) {
            $navgiation = new static;
            $navgiation->load($this->storage['parent_id']);
            return $navgiation;
        }
        return NULL;
    }

    public function getChildrenCategories()
    {
        if (isset($this->storage['id'])) {
            $collection = new Collection;
            $collection->where(['parent_id' => $this->storage['id']]);
            return $collection;
        }
        return NULL;
    }

    public function getPages()
    {
        if (isset($this->storage['id'])) {
            $pages = new PageCollection($this->languageId);
            $tableGateway = $this->getTableGateway('cms_category_page');
            $result = $tableGateway->select(['category_id' => $this->storage['id']])->toArray();
            $valueSet = [];
            array_walk($result, function ($item) use (&$valueSet) {
                $valueSet[] = $item['page_id'];
            });
            if (count($valueSet)) {
                $pages->where(new In('id', $valueSet));
                return $pages;
            }
            return $pages;
        }
        return [];
    }
    
    public function getImage()
    {
        if (!empty($this->storage['image'])) {
            $resource = new Resource;
            $resource->load($this->storage['image']);
            return $resource['real_name'];
        }
        return $this->getPubUrl('frontend/images/placeholder.png');
    }

    public function getThumbnail()
    {
        if (!empty($this->storage['thumbnail'])) {
            $resource = new Resource;
            $resource->load($this->storage['thumbnail']);
            return $resource['real_name'];
        }
        return $this->getPubUrl('frontend/images/placeholder.png');
    }

    public function getUrl()
    {
        if (!isset($this->storage['path'])) {
            $constraint = ['product_id' => null, 'category_id' => $this->getId()];
            $result = $this->getContainer()->get('indexer')->select('cms_url', $this->languageId, $constraint);
            $this->storage['path'] = isset($result[0]) ? $result[0]['path'] . '.html' : '';
        }
        return $this->getBaseUrl($this->storage['path']);
    }

    protected function beforeSave()
    {
        $this->storage['uri_key'] = rawurlencode($this->storage['uri_key']);
        $this->beginTransaction();
        parent::beforeSave();
    }

    protected function afterSave()
    {
        parent::afterSave();
        if (isset($this->storage['name'])) {
            $tableGateway = $this->getTableGateway('cms_category_language');
            foreach ((array) $this->storage['name'] as $languageId => $name) {
                $this->upsert(['name' => $name], ['category_id' => $this->getId(), 'language_id' => $languageId], $tableGateway);
            }
        }
        $this->commit();
    }

    protected function beforeLoad($select)
    {
        $select->join('cms_category_language', 'cms_category_language.category_id=cms_category.id', ['name'], 'left');
        $select->join('core_language', 'cms_category_language.language_id=core_language.id', ['language_id' => 'id', 'language' => 'name'], 'left');
        parent::beforeLoad($select);
    }

    protected function afterLoad(&$result)
    {
        if (isset($result[0])) {
            $language = [];
            $name = [];
            foreach ($result as $item) {
                $language[$item['language_id']] = $item['language'];
                $name[$item['language_id']] = $item['name'];
            }
            $result[0]['language'] = $language;
            $result[0]['language_id'] = array_keys($language);
            $result[0]['name'] = $name;
        }
        parent::afterLoad($result);
    }

}
