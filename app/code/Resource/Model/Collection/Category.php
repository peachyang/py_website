<?php

namespace Seahinet\Resource\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;
use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Sql\Predicate\In;
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
     
        foreach ($this->storage as $key => $item) {
            $ids[] = $item['id'];
            $data[$item['id']] = $item;
           
        }

        $languages = new Language;
        $languages->join('resource_category_language', 'core_language.id=resource_category_language.language_id', ['category_id'], 'right')
        ->columns(['language_id' => 'id', 'language' => 'code'])
        ->where(new In('category_id', $ids));
        //echo $languages->getSqlString($this->getContainer()->get('dbAdapter')->getPlatform());
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
