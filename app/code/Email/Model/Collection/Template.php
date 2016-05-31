<?php

namespace Seahinet\Email\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;
use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Sql\Predicate\In;

class Template extends AbstractCollection
{

    protected function construct()
    {
        $this->init('email_template');
    }

    protected function afterLoad($result)
    {
        parent::afterLoad($result);
        $ids = [];
        $data = [];
        foreach ($this->storage as $key => $item) {
            $content = @gzdecode($item['content']);
            if (isset($item['id'])) {
                $ids[] = $item['id'];
                $data[$item['id']] = $item;
                $data[$item['id']]['language'] = [];
                if ($content !== false) {
                    $data[$item['id']]['content'] = $content;
                }
            } else if ($content !== false) {
                $this->storage[$key]['content'] = $content;
            }
        }
        if (!empty($ids)) {
            $languages = new Language;
            $languages->join('email_template_language', 'core_language.id=email_template_language.language_id', ['template_id'], 'right')
                    ->columns(['language_id' => 'id', 'language' => 'code'])
                    ->where(new In('template_id', $ids));
            $languages->load(false);
            foreach ($languages as $item) {
                if (isset($data[$item['template_id']])) {
                    $data[$item['template_id']]['language'][$item['language_id']] = $item['language'];
                }
            }
            $this->storage = array_values($data);
        }
    }

}
