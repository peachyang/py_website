<?php

namespace Seahinet\CMS\Model;

use Seahinet\Lib\Model\AbstractModel;

class Page extends AbstractModel
{

    public function _construct()
    {
        $this->init('cms_page', 'id', ['id', 'parent_id', 'status', 'uri_key', 'title', 'keywords', 'description', 'content']);
        $this->withLanguage('cms_page_language', 'page_id');
    }

    protected function beforeSave()
    {
        $this->storage['content'] = gzdeflate($this->storage['content']);
        parent::beforeSave();
    }

    protected function afterLoad()
    {
        $this->storage['content'] = @gzinflate($this->storage['content']);
        parent::afterLoad();
    }

}
