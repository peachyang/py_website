<?php

namespace Seahinet\Resource\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;
use Seahinet\Lib\Session\Segment;


/**
 * System backend Resource category
 */
class Category extends AbstractCollection
{


    protected function construct()
    {
        $this->init('resource_category');
    }

    protected function afterLoad()
    {
        $ids = [];
        $data = [];
     
        $languages = new Language;
        $languages->join('Resource_category_language', 'core_language.id=Resource_category_language.language_id', ['category_id'], 'right')
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
