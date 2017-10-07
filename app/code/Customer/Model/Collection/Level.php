<?php

namespace Seahinet\Customer\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;
use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Sql\Predicate\In;

class Level extends AbstractCollection
{

    protected function construct()
    {
        $this->init('customer_level');
    }

    protected function afterLoad(&$result)
    {
        if (isset($result[0]['id'])) {
            $ids = [];
            $data = [];
            foreach ($result as $item) {
                $ids[] = $item['id'];
                $data[$item['id']] = $item;
                $data[$item['id']]['language'] = [];
                $data[$item['id']]['name'] = [];
            }
            $languages = new Language;
            $languages->join('customer_level_language', 'core_language.id=customer_level_language.language_id', ['level_id', 'name'], 'right')
                    ->columns(['language_id' => 'id', 'language' => 'code'])
                    ->where(new In('level_id', $ids));
            $languages->load(false);
            foreach ($languages as $item) {
                if (isset($data[$item['level_id']])) {
                    $data[$item['level_id']]['language'][$item['language_id']] = $item['language'];
                    $data[$item['level_id']]['name'][$item['language_id']] = $item['name'];
                }
            }
            $result = array_values($data);
        }
        parent::afterLoad($result);
    }

}
