<?php

namespace Seahinet\Cms\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Block extends AbstractCollection
{

    public function _construct()
    {
        $this->init('cms_block');
        $this->select->join('cms_block_language', 'cms_block.id=cms_block_language.block_id', [], 'left');
        $this->select->join('core_language', 'core_language.id=cms_block_language.language_id', ['language_id' => 'id', 'language' => 'code'], 'left');
    }

    protected function afterLoad()
    {
        $data = [];
        foreach ($this->storage as $item) {
            if (isset($data[$item['id']])) {
                $data[$item['id']]['language'][$item['language_id']] = $item['language'];
            } else {
                $data[$item['id']] = $item;
                $data[$item['id']]['language'] = [$item['language_id'] => $item['language']];
                $content = @gzdecode($item['content']);
                if ($content !== false) {
                    $data[$item['id']]['content'] = $content;
                }
            }
        }
        $this->storage = $data;
        parent::afterLoad();
    }

}
