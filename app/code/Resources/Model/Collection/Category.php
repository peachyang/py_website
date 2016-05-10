<?php

namespace Seahinet\Resources\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;
use Seahinet\Lib\Session\Segment;


/**
 * System backend Resources category
 */
class Category extends AbstractCollection
{


    protected function _construct()
    {
        $this->init('resources_category');
    }

    protected function afterLoad()
    {
        $ids = [];
        $data = [];
     
        $languages = new Language;
        $languages->join('resources_category_language', 'core_language.id=resources_category_language.language_id', ['category_id'], 'right')
        ->columns(['language_id' => 'id', 'language' => 'code'])
        ->where(new In('category_id', $ids));
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
