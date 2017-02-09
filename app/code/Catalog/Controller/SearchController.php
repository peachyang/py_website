<?php

namespace Seahinet\Catalog\Controller;

use Seahinet\Catalog\Model\Collection\Product;
use Seahinet\Lib\Bootstrap;
use Zend\Db\Sql\Predicate\In;

class SearchController extends CategoryController
{

    use \Seahinet\Search\Traits\Engine;

    public function indexAction()
    {
        $data = $this->getRequest()->getQuery();
        $root = $this->getLayout('catalog_category');
        $content = $root->getChild('content');
        $languageId = Bootstrap::getLanguage()->getId();
        if (isset($data['q'])) {
            $engine = $this->getSearchEngineHandler();
            $result = $engine->select('catalog_search', $data, $languageId);
            $ids = [];
            foreach ($result as $item) {
                $ids[] = $item['id'];
            }
            $crumb = $this->translate('Search Result: %s', [$this->getRequest()->getQuery('q')]);
            $root->getChild('head')->setTitle($crumb)
                    ->setKeywords(str_replace(' ', ',', $data['q']));
            $content->getChild('breadcrumb')->addCrumb(['label' => $crumb]);
        }
        $products = new Product($languageId);
        $products->where(empty($ids) ? '0' : new In('id', $ids));
        $products = $this->prepareCollection($products);
        $content->getChild('toolbar')->setCollection($products);
        $content->getChild('list')->setProducts($products);
        $content->getChild('toolbar_bottom')->setCollection($products);
        return $root;
    }

}
