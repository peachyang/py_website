<?php

namespace Seahinet\Cms\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Resource\Model\Collection\Resource;
use Zend\Db\TableGateway\TableGateway;

class Page extends AbstractModel
{

    public function construct()
    {
        $this->init('cms_page', 'id', ['id', 'store_id', 'status', 'uri_key', 'title', 'keywords', 'description', 'thumbnail', 'image', 'content']);
    }

    protected function beforeSave()
    {
        $this->storage['content'] = gzencode($this->storage['content']);
        $this->beginTransaction();
        parent::beforeSave();
    }

    protected function afterSave()
    {
        if (isset($this->storage['language_id'])) {
            $tableGateway = new TableGateway('cms_page_language', $this->getContainer()->get('dbAdapter'));
            $tableGateway->delete(['page_id' => $this->getId()]);
            foreach ($this->storage['language_id'] as $language_id) {
                $tableGateway->insert(['page_id' => $this->getId(), 'language_id' => $language_id]);
            }
        }
        if (isset($this->storage['category_id'])) {
            $tableGateway = new TableGateway('cms_category_page', $this->getContainer()->get('dbAdapter'));
            $tableGateway->delete(['page_id' => $this->getId()]);
            foreach ($this->storage['category_id'] as $category_id) {
                $tableGateway->insert(['page_id' => $this->getId(), 'category_id' => $category_id]);
            }
        }
        parent::afterSave();
        $this->commit();
    }

    protected function beforeLoad($select)
    {
        $select->join('cms_page_language', 'cms_page_language.page_id=cms_page.id', [], 'left');
        $select->join('core_language', 'cms_page_language.language_id=core_language.id', ['language_id' => 'id', 'language' => 'name'], 'left');
        $select->join('cms_category_page', 'cms_category_page.page_id=cms_page.id', [], 'left');
        $select->join('cms_category', 'cms_category.id=cms_category_page.category_id', ['category_id' => 'id'], 'left');
        $select->join('cms_category_language', 'cms_category.id=cms_category_language.category_id', ['category' => 'name'], 'left');
        parent::beforeLoad($select);
    }

    protected function afterLoad(&$result)
    {
        if (isset($result[0]['id'])) {
            $language = [];
            $category = [];
            foreach ($result as $item) {
                $language[$item['language_id']] = $item['language'];
                $category[$item['category_id']] = $item['category'];
            }
            $result[0]['language'] = $language;
            $result[0]['language_id'] = array_keys($language);
            $result[0]['category'] = $category;
            $result[0]['category_id'] = array_keys($category);
            $data = @gzdecode($result[0]['content']);
            if ($data !== false) {
                $result[0]['content'] = $data;
            }
        }
        parent::afterLoad($result);
    }

    public function getImage()
    {
        if ($this->isLoaded && !empty($this->storage['image'])) {
            $collection = new Resource;
            $collection->where(['id' => $this->storage['image']]);
            return $collection;
        }
        return null;
    }

    public function getThumbnail()
    {
        if ($this->isLoaded && !empty($this->storage['thumbnail'])) {
            $collection = new Resource;
            $collection->where(['id' => $this->storage['thumbnail']]);
            return $collection;
        }
        return null;
    }

}
