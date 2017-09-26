<?php

namespace Seahinet\Cms\Model;

use Seahinet\Cms\Model\Collection\Category as Collection;
use Seahinet\Cms\Model\Collection\Page as PageCollection;
use Seahinet\Lib\Model\AbstractModel;

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
            $pages = new PageCollection;
            $pages->join('cms_category_page', 'cms_page.id=cms_category_page.page_id', [])
                    ->where(['cms_category_page.category_id' => $this->storage['id']])
                    ->order('id DESC');
            return $pages;
        }
        return NULL;
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
