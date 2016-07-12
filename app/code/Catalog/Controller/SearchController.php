<?php

namespace Seahinet\Catalog\Controller;

use Seahinet\Catalog\Model\Collection\Product;
use Seahinet\Lib\Bootstrap;
use Zend\Db\Sql\Predicate\In;
use Zend\Db\Sql\Where;

class SearchController extends CategoryController
{

    public function indexAction()
    {
        $query = explode(' ', $this->getRequest()->getQuery('q'));
        $where = new Where;
        foreach ($query as $q) {
            $where->like('data', '%' . $q . '%');
        }
        $languageId = Bootstrap::getLanguage()->getId();
        $result = $this->getContainer()->get('indexer')->select('catalog_search', $languageId, $where);
        $ids = [];
        foreach ($result as $item) {
            $ids[] = $item['id'];
        }
        $root = $this->getLayout('catalog_category');
        $crumb = $this->translate('Search Result: %s', [$this->getRequest()->getQuery('q')]);
        $root->getChild('head')->setTitle($crumb)
                ->setKeywords(implode(',', $query));
        $content = $root->getChild('content');
        $content->getChild('breadcrumb')->addCrumb(['label' => $crumb]);
        $products = new Product($languageId);
        $products->where(new In('id', $ids));
        $products = $this->prepareCollection($products);
        $content->getChild('toolbar')->setCollection($products);
        $content->getChild('list')->setProducts($products);
        $content->getChild('toolbar_bottom')->setCollection($products);
        return $root;
    }

}
