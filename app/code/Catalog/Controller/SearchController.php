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
        $data = $this->getRequest()->getQuery();
        $root = $this->getLayout('catalog_category');
        $content = $root->getChild('content');
        $languageId = Bootstrap::getLanguage()->getId();
        $config = $this->getContainer()->get('config');
        if (isset($data['q'])) {
            $query = explode(' ', $data['q']);
            $where = new Where;
            if (!empty($data['store_id'])) {
                $where->equalTo('store_id', $data['store_id']);
            }
            foreach ($query as $q) {
                $where->like('data', '%' . $q . '%');
            }
            $options = [];
            $options['limit'] = (int) ($data['limit'] ?? empty($data['mode']) ?
                    $config['catalog/frontend/default_per_page_grid'] :
                    $config['catalog/frontend/default_per_page_' . $data['mode']]);
            if (isset($data['page'])) {
                $options['offset'] = (int) ($data['page'] - 1) * $options['limit'];
            }
            $result = $this->getContainer()->get('indexer')->select('catalog_search', $languageId, $where, $options);
            $ids = [];
            foreach ($result as $item) {
                $ids[] = $item['id'];
            }
            $crumb = $this->translate('Search Result: %s', [$this->getRequest()->getQuery('q')]);
            $root->getChild('head')->setTitle($crumb)
                    ->setKeywords(implode(',', $query));
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
