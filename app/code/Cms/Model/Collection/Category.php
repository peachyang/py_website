<?php

namespace Seahinet\Cms\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;
use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Sql\Predicate\In;

class Category extends AbstractCollection
{

    protected $withLanguage = false;

    protected function construct()
    {
        $this->init('cms_category');
    }

    protected function afterLoad($result)
    {
        parent::afterLoad($result);
        if (isset($this->storage[0]['id'])) {
            $ids = [];
            $data = [];
            foreach ($this->storage as $item) {
                $ids[] = $item['id'];
                $data[$item['id']] = $item;
                $data[$item['id']]['language'] = [];
                $data[$item['id']]['name'] = [];
            }
            $languages = new Language;
            $languages->join('cms_category_language', 'core_language.id=cms_category_language.language_id', ['category_id', 'name'], 'right')
                    ->columns(['language_id' => 'id', 'language' => 'code'])
                    ->where(new In('category_id', $ids));
            $languages->load(false);
            foreach ($languages as $item) {
                if (isset($data[$item['category_id']])) {
                    $data[$item['category_id']]['language'][$item['language_id']] = $item['language'];
                    $data[$item['category_id']]['name'][$item['language_id']] = $item['name'];
                }
            }
            $this->storage = array_values($data);
        }
    }

}
