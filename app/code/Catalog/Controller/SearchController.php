<?php

namespace Seahinet\Catalog\Controller;

use Exception;
use Seahinet\Catalog\Model\{
    Collection\Product,
    SearchTerm
};
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Session\Segment;
use Seahinet\Search\Model\Factory;
use Zend\Db\Sql\Predicate\In;

class SearchController extends CategoryController
{

    public function indexAction()
    {
        $interval = (int) $this->getContainer()->get('config')['catalog/product/search_interval'];
        $segment = new Segment('core');
        if ($interval && time() - $segment->get('lastSearch', 0) < $interval) {
            $segment->addMessage(['message' => $this->translate('The administrator limits the search interval to %ds, please try it later.', [$interval]), 'level' => 'danger']);
            return $this->redirectReferer();
        }
        $data = $this->getRequest()->getQuery();
        $root = $this->getLayout('catalog_category');
        $content = $root->getChild('content');
        $languageId = Bootstrap::getLanguage()->getId();
        if (isset($data['q'])) {
            $engine = (new Factory)->getSearchEngineHandler();
            $result = $engine->select('catalog_search', $data, $languageId);
            $ids = [];
            foreach ($result as $item) {
                $ids[$item['id']] = $item['weight'] ?? 0;
            }
            $this->saveTerm($data, $ids);
            $crumb = $this->translate('Search Result: %s', [$this->getRequest()->getQuery('q')]);
            $root->getChild('head')->setTitle($crumb)
                    ->setKeywords(str_replace(' ', ',', $data['q']));
            $content->getChild('breadcrumb')->addCrumb(['label' => $crumb]);
        }
        $products = new Product($languageId);
        $products->where(empty($ids) ? '0' : new In('id', array_keys($ids)));
        $products = $this->prepareCollection($products);
        $content->getChild('toolbar')->setCollection($products);
        $content->getChild('list')->setProducts($products);
        $content->getChild('toolbar_bottom')->setCollection($products);
        $segment->set('lastSearch', time());
        return $root;
    }

    protected function saveTerm($data, $ids)
    {
        try {
            $term = new SearchTerm;
            $term->load($data['q']);
            if ($term->getId()) {
                $term->setData('popularity', (int) $term->offsetGet('popularity') + 1);
            } else {
                $term->setData([
                    'term' => $data['q'],
                    'count' => count($ids),
                    'store_id' => $data['store_id'] ?? null,
                    'category_id' => $data['category_id'] ?? null,
                    'status' => count($ids) ? 1 : 0
                ]);
            }
            $term->save();
        } catch (Exception $e) {
            $this->getContainer()->get('log')->logException($e);
        }
    }

}
