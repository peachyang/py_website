<?php

namespace Seahinet\Cms\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;
use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Sql\Predicate\In;

class Page extends AbstractCollection
{

    public function construct()
    {
        $this->init('cms_page');
    }

    protected function afterLoad()
    {
        $ids = [];
        $data = [];
        foreach ($this->storage as $item) {
            $ids[] = $item['id'];
            $data[$item['id']] = $item;
            $data[$item['id']]['language'] = [];
            $content = @gzdecode($item['content']);
            if ($content !== false) {
                $data[$item['id']]['content'] = $content;
            }
        }
        $languages = new Language;
        $languages->join('cms_page_language', 'core_language.id=cms_page_language.language_id', ['page_id'], 'right')
                ->columns(['language_id' => 'id', 'language' => 'code'])
                ->where(new In('page_id', $ids));
        $languages->load(false);
        foreach ($languages as $item) {
            if (isset($data[$item['page_id']])) {
                $data[$item['page_id']]['language'][$item['language_id']] = $item['language'];
            }
        }
        $this->storage = array_values($data);
        parent::afterLoad();
    }

}
