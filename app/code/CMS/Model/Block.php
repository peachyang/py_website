<?php

namespace Seahinet\CMS\Model;

use Seahinet\Lib\Model\AbstractModel;

class Block extends AbstractModel
{

    public function _construct()
    {
        $this->init('cms_block', 'id', ['id', 'status', 'code', 'content']);
        $this->withLanguage('cms_block_language', 'block_id');
    }

    protected function beforeSave()
    {
        $this->storage['content'] = gzencode($this->storage['content']);
        parent::beforeSave();
    }

    protected function afterLoad()
    {
        $data = @gzdecode($this->storage['content']);
        if ($data !== false) {
            $this->storage['content'] = $data;
        }
        parent::afterLoad();
    }

}
