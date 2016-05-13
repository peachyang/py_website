<?php

namespace Seahinet\Cms\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;
use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Sql\Predicate\In;

class Block extends AbstractCollection
{

    public function construct()
    {
        $this->init('cms_block');
        $this->select->join('cms_block_language', 'cms_block.id=cms_block_language.block_id', [], 'left');
        $this->select->join('core_language', 'core_language.id=cms_block_language.language_id', ['language_id' => 'id', 'language' => 'code'], 'left');
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
        $languages->join('cms_block_language', 'core_language.id=cms_block_language.language_id', ['block_id'], 'right')
                ->columns(['language_id' => 'id', 'language' => 'code'])
                ->where(new In('block_id', $ids));
        $languages->load(false);
        foreach ($languages as $item) {
            if (isset($data[$item['block_id']])) {
                $data[$item['block_id']]['language'][$item['language_id']] = $item['language'];
            }
        }
        $this->storage = $data;
        parent::afterLoad();
    }

}
