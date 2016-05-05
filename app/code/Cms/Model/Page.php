<?php

namespace Seahinet\Cms\Model;

use Seahinet\Lib\Model\AbstractModel;
use Zend\Db\TableGateway\TableGateway;

class Page extends AbstractModel
{

    public function _construct()
    {
        $this->init('cms_page', 'id', ['id', 'parent_id', 'store_id', 'status', 'uri_key', 'title', 'keywords', 'description', 'thumbnail', 'image', 'content']);
    }

    protected function beforeSave()
    {
        $this->storage['content'] = gzencode($this->storage['content']);
        $this->beginTransaction();
        parent::beforeSave($columns);
    }

    protected function afterSave()
    {
        if (isset($this->storage['language_id'])) {
            $tableGateway = new TableGateway('cms_page_language');
            $tableGateway->delete(['page_id' => $this->getId()]);
            foreach ($this->storage['language_id'] as $language_id) {
                $tableGateway->insert(['page_id' => $this->getId(), 'language_id' => $language_id]);
            }
        }
        parent::afterSave();
        $this->commit();
    }

    protected function beforeLoad($select)
    {
        $select->join('cms_page_language', 'cms_page_language.page_id=cms_page.id', [], 'left');
        $select->join('core_language', 'cms_page_language.language_id=core_language.id', ['language_id' => 'id', 'language' => 'name'], 'left');
        parent::beforeLoad($select);
    }

    protected function afterLoad($result = [])
    {
        parent::afterLoad();
        if (isset($result[0])) {
            $language = [];
            foreach ($result as $item) {
                $language[$item['language_id']] = $item['language'];
            }
            $this->storage['language'] = $language;
        }
        $data = @gzdecode($this->storage['content']);
        if ($data !== false) {
            $this->storage['content'] = $data;
        }
    }

}
