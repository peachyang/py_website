<?php

namespace Seahinet\Resource\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;
use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Sql\Predicate\In;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Bootstrap;


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
        $languages->join('resource_category_language', 'core_language.id=resource_category_language.language_id', ['category_id', 'name'], 'right')
                  ->columns(['language_id' => 'id', 'language' => 'code','language_name' => 'name'])
                  ->where(new In('category_id', $ids));
        //echo $languages->getSqlString($this->getContainer()->get('dbAdapter')->getPlatform());
        $languages->load(false);
        foreach ($languages as $item) {
            if (isset($data[$item['category_id']])) {
                $data[$item['category_id']]['language'][$item['language_id']] = $item['language'];
                $data[$item['category_id']]['name'][$item['language_id']] = $item['name'];
                $data[$item['category_id']]['language_name'][$item['language_id']] = $item['language_name'];
                
            }
        }
        
        $this->storage = array_values($data);
        //exit();
        parent::afterLoad();
    }  
    
    
}
