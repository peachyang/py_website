<?php

namespace Seahinet\Resource\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;
use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Sql\Predicate\In;

/**
 * System backend Resource category
 */
class Resource extends AbstractCollection
{

    protected function construct()
    {
        $this->init('resource');
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
            }
            $languages = new Language;
            $languages->join('resource_category_language', 'core_language.id=resource_category_language.language_id', ['category_id'], 'right')
                    ->columns(['language_id' => 'id', 'language' => 'code'])
                    ->where(new In('category_id', $ids));
            $languages->load(false);
            foreach ($languages as $item) {
                if (isset($data[$item['category_id']])) {
                    $data[$item['category_id']]['language'][$item['language_id']] = $item['language'];
                }
            }
            $this->storage = array_values($data);
        }
    }

}
