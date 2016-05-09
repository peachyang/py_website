<?php

namespace Seahinet\Message\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;
use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Sql\Predicate\In;

class Template extends AbstractCollection
{

    protected function construct()
    {
        $this->init('message_template');
    }

    protected function afterLoad()
    {
        $ids = [];
        $data = [];
        foreach ($this->storage as $key => $item) {
            $ids[] = $item['id'];
            $data[$item['id']] = $item;
            $content = @gzdecode($item['content']);
            if ($content !== false) {
                $data[$item['id']]['content'] = $content;
            }
        }
        $languages = new Language;
        $languages->join('message_template_language', 'core_language.id=message_template_language.language_id', ['template_id'], 'right')
                ->columns(['language_id' => 'id', 'language' => 'code'])
                ->where(new In('template_id', $ids));
        $languages->load(false);
        foreach ($languages as $item) {
            if (isset($data[$item['template_id']])) {
                $data[$item['template_id']]['language'][$item['language_id']] = $item['language'];
            }
        }
        $this->storage = array_values($data);
        parent::afterLoad();
    }

}
