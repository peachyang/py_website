<?php

namespace Seahinet\Cms\Traits;

use Seahinet\Cms\Model\Category;
use Seahinet\Cms\Model\Page;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\ViewModel\Breadcrumb as ViewModel;

trait Breadcrumb
{

    /**
     * @param ViewModel $breadcrumb
     * @param int $categoryId
     * @param int $pageId
     */
    protected function generateCrumbs(ViewModel $breadcrumb, $categoryId, $pageId = null)
    {
        $indexer = $this->getContainer()->get('indexer');
        $languageId = Bootstrap::getLanguage()->getId();
        $this->addCrumb($breadcrumb, $categoryId, $indexer, $languageId);
        if ($pageId) {
            $result = $indexer->select('cms_url', Bootstrap::getLanguage()->getId(), [
                'category_id' => $categoryId,
                'page_id' => $pageId
            ]);
            $page = new Page($languageId);
            $page->load($pageId);
            $breadcrumb->addCrumb([
                'link' => $result[0]['path'] . '.html',
                'label' => $page['title']
            ]);
        }
    }

    protected function addCrumb(ViewModel $breadcrumb, $categoryId, $indexer = null, $languageId = null)
    {
        if (is_null($indexer)) {
            $indexer = $this->getContainer()->get('indexer');
        }
        if (is_null($languageId)) {
            $languageId = Bootstrap::getLanguage()->getId();
        }
        $category = new Category($languageId);
        $category->load($categoryId);
        if ($category['parent_id']) {
            $this->addCrumb($breadcrumb, $category['parent_id'], $indexer);
            $breadcrumb->addCrumb([
                'link' => $indexer->select('cms_url', $languageId, [
                    'category_id' => $categoryId,
                    'page_id' => null
                ])[0]['path'] . '.html',
                'label' => $category['title']
            ]);
        }
    }

}
