<?php

namespace Seahinet\Cms\Model;

use Seahinet\Lib\Model\AbstractModel;

class Category extends AbstractModel
{

    protected function construct()
    {
        $this->init('cms_category', 'id', ['id', 'name', 'uri_key']);
    }

    protected function afterSave()
    {
        if (isset($this->storage['language_id'])) {
            $tableGateway = new TableGateway('cms_category_language', $this->getContainer()->get('dbAdapter'));
            $tableGateway->delete(['category_id' => $this->getId()]);
            foreach ($this->storage['language_id'] as $language_id) {
                $tableGateway->insert(['category_id' => $this->getId(), 'language_id' => $language_id]);
            }
        }
        parent::afterSave();
        $this->commit();
    }

    protected function beforeLoad($select)
    {
        $select->join('cms_category_language', 'cms_category_language.category_id=cms_category.id', [], 'left');
        $select->join('core_language', 'cms_category_language.language_id=core_language.id', ['language_id' => 'id', 'language' => 'name'], 'left');
        parent::beforeLoad($select);
    }

    protected function afterLoad($result = [])
    {
        parent::afterLoad($result);
        if (isset($result[0])) {
            $language = [];
            foreach ($result as $item) {
                $language[$item['language_id']] = $item['language'];
            }
            $this->storage['language'] = $language;
            $this->storage['language_id'] = array_keys($language);
        }
    }

}
