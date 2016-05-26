<?php

namespace Seahinet\Cms\Model;

use Seahinet\Lib\Model\AbstractModel;
use Zend\Db\TableGateway\TableGateway;

class Category extends AbstractModel
{

    protected function construct()
    {
        $this->init('cms_category', 'id', ['id', 'uri_key', 'status', 'parent_id']);
    }

    protected function beforeSave()
    {
        $this->beginTransaction();
        parent::beforeSave();
    }

    protected function afterSave()
    {
        parent::afterSave();
        if (isset($this->storage['name'])) {
            $tableGateway = new TableGateway('cms_category_language', $this->getContainer()->get('dbAdapter'));
            foreach ((array) $this->storage['name'] as $language_id => $name) {
                $this->upsert(['name' => $name], ['category_id' => $this->getId(), 'language_id' => $language_id], $tableGateway);
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

    protected function afterLoad($result = [])
    {
        parent::afterLoad($result);
        if (isset($result[0])) {
            $language = [];
            $name = [];
            foreach ($result as $item) {
                $language[$item['language_id']] = $item['language'];
                $name[$item['language_id']] = $item['name'];
            }
            $this->storage['language'] = $language;
            $this->storage['language_id'] = array_keys($language);
            $this->storage['name'] = $name;
        }
    }

}
