<?php

namespace Seahinet\Customer\Model;

use Seahinet\Lib\Model\AbstractModel;
use Zend\Db\TableGateway\TableGateway;

class Level extends AbstractModel
{

    protected function construct()
    {
        $this->init('customer_level', 'id', ['id', 'level']);
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
            $tableGateway = new TableGateway('customer_level_language', $this->getContainer()->get('dbAdapter'));
            foreach ((array) $this->storage['name'] as $language_id => $name) {
                $this->upsert(['name' => $name], ['level_id' => $this->getId(), 'language_id' => $language_id], $tableGateway);
            }
        }
        $this->commit();
    }

    protected function beforeLoad($select)
    {
        $select->join('customer_level_language', 'customer_level_language.level_id=customer_level.id', ['name'], 'left');
        $select->join('core_language', 'customer_level_language.language_id=core_language.id', ['language_id' => 'id', 'language' => 'name'], 'left');
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
