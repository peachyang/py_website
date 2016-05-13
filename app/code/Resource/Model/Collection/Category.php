<?php

namespace Seahinet\Resource\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;
use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Sql\Predicate\In;
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
        //$languages->join('resource_category_language', 'core_language.id=resource_category_language.language_id', ['category_id'], 'left')
        //->columns(['language_id' => 'id', 'language' => 'code'])
        //->where(new In('category_id', $ids))
        
        $cagoryName=$this->select
                         ->from(['c'=>'resource_category_language'],['name'])
                         ->join(['l'=>'core_language'],'c.language_id=l.id')
                         ->where(new In('category_id', $ids))
                         ->where(["l.language_id"=>Bootstrap::getLanguage()->getId()]);
        echo '======================';
        exit('**************');
        echo $cagoryName->getSqlString($this->getContainer()->get('dbAdapter')->getPlatform());
        exit('test!');
        $languages->load(false);
        foreach ($languages as $item) {
            if (isset($data[$item['category_id']])) {
                $data[$item['category_id']]['language'][$item['language_id']] = $item['language'];
            }
        }
        print_r($data);
        //exit();
        $this->storage = array_values($data);
        parent::afterLoad();
    }  
    
    
}
