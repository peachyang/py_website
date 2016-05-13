<?php

namespace Seahinet\Cms\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;

class Category extends AbstractCollection
{

    protected $withLanguage = false;

    protected function construct()
    {
        $this->init('cms_category');
    }

    public function withLanguage()
    {
        $this->withLanguage = true;
        $this->select->join('cms_category_language', 'cms_category_language.category_id=cms_category.id', ['name'], 'left')
                ->join('core_language', 'cms_category_language.language_id=core_language.id', ['language_id' => 'id', 'language' => 'name'], 'left');
    }

    protected function afterLoad()
    {
        if ($this->withLanguage) {
            $data = [];
            foreach ($this->storage as $item) {
                if (!isset($data[$item['id']])) {
                    $data[$item['id']] = $item;
                    $data[$item['id']]['name'] = [];
                    $data[$item['id']]['language'] = [];
                }
                $data[$item['id']]['name'][$item['language_id']] = $item['name'];
                $data[$item['id']]['language'][$item['language_id']] = $item['language'];
            }
            $this->storage = $data;
        }
        parent::afterLoad();
    }

}
