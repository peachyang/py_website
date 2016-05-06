<?php

namespace Seahinet\Cms\Model;

use Seahinet\Lib\Model\AbstractModel;

class Block extends AbstractModel
{

    public function _construct()
    {
        $this->init('cms_block', 'id', ['id', 'store_id', 'status', 'code', 'content']);
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
            $tableGateway = new TableGateway('cms_block_language', $this->getContainer()->get('dbAdapter'));
            $tableGateway->delete(['block_id' => $this->getId()]);
            foreach ($this->storage['language_id'] as $language_id) {
                $tableGateway->insert(['block_id' => $this->getId(), 'language_id' => $language_id]);
            }
        }
        parent::afterSave();
        $this->commit();
    }

    protected function beforeLoad($select)
    {
        $select->join('cms_block_language', 'cms_block_language.block_id=cms_block.id', [], 'left');
        $select->join('core_language', 'cms_block_language.language_id=core_language.id', ['language_id' => 'id', 'language' => 'name'], 'left');
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
