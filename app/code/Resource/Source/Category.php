<?php

namespace Seahinet\Resource\Source;

use Seahinet\Resource\Model\Collection\Category as Collection;
use Seahinet\Lib\Source\SourceInterface;
use Zend\Db\Sql\Predicate\NotIn;
use Seahinet\Lib\Bootstrap;

class Category implements SourceInterface
{
    use \Seahinet\Lib\Traits\Container;
    public function getSourceArray($except = [])
    {
        $collection = new Collection;
        if (!empty($except)) {
            $collection->where(new NotIn('resource_category.id', (array) $except));
        }
        $result = [];
        foreach ($collection as $category) {
            $result[$category['id']] = $category['name'][Bootstrap::getLanguage()->getId()];
        }
        return $result;
    }
    
    public function getNameArray($except = []){
        $collection = new Collection;
        $result = [];
        $languages=$collection->toArray()[0]['language'];
        $languages_names=$collection->toArray()[0]['language_name'];
        $names=$collection->toArray()[0]['name'];
        foreach ($languages as $k => $c) {
            $result[$k] = ['name'=>$names[$k],'language_name'=>$languages_names[$k]];
        }
        return $result;
    }
    
    public function getLanguageIdArray($except = []){
        $collection = new Collection;
        $result = [];
        $languages=$collection->toArray()[0]['language'];
        foreach ($languages as $k => $c) {
            $result[] = $k;
        }
        return $result;
    }
    public function getParentIdArray($except = []){
        $collection = new Collection;
        $result = [];
        $result=$collection->toArray()[0]['parent_id'];
        return $result;
    }
    
}
