<?php

namespace Seahinet\Cms\Model;

use Seahinet\Lib\Model\AbstractModel;
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

    protected function afterLoad($result = [])
    {
        parent::afterLoad($result);
        if (isset($result[0])) {
            $language = [];
            $category = [];
            foreach ($result as $item) {
                $language[$item['language_id']] = $item['language'];
                $category[$item['category_id']] = $item['category'];
            }
            $this->storage['language'] = $language;
            $this->storage['language_id'] = array_keys($language);
            $this->storage['category'] = $category;
            $this->storage['category_id'] = array_keys($category);
        }
        $data = @gzdecode($this->storage['content']);
        if ($data !== false) {
            $this->storage['content'] = $data;
        }
    }

}
