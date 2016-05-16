<?php

namespace Seahinet\Cms\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;
use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\TableGateway\TableGateway;
use Seahinet\Lib\Bootstrap;
use Zend\Db\Sql\Predicate\In;

class Page extends AbstractCollection
{

    public function construct()
    {
        $this->init('cms_page');
    }

    protected function afterLoad()
    {
        $ids = [];
        $data = [];
        foreach ($this->storage as $item) {
            $ids[] = $item['id'];
            $data[$item['id']] = $item;
            $data[$item['id']]['language'] = [];
            $data[$item['id']]['category'] = [];
            $content = @gzdecode($item['content']);
            if ($content !== false) {
                $data[$item['id']]['content'] = $content;
            }
        }
        $languages = new Language;
        $languages->join('cms_page_language', 'core_language.id=cms_page_language.language_id', ['page_id'], 'right')
                ->columns(['language_id' => 'id', 'language' => 'code'])
                ->where(new In('page_id', $ids));
        $languages->load(false);
        foreach ($languages as $item) {
            if (isset($data[$item['page_id']])) {
                $data[$item['page_id']]['language'][$item['language_id']] = $item['language'];
            }
        }
        $tableGateway = new TableGateway('cms_category_page', $this->getContainer()->get('dbAdapter'));
        $select = $tableGateway->getSql()->select();
        $select->join('cms_category_language', 'cms_category_page.category_id=cms_category_language.category_id', ['name'], 'left')
                ->where(new In('page_id', $ids))
                ->where(['language_id' => Bootstrap::getLanguage()->getId()]);
        $category = $tableGateway->selectWith($select);
        foreach ($category as $item) {
            if (isset($data[$item['page_id']])) {
                $data[$item['page_id']]['category'][$item['category_id']] = $item['name'];
            }
        }
        $this->storage = array_values($data);
        parent::afterLoad();
    }

}
