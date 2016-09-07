<?php

namespace Seahinet\Catalog\Controller;

use Seahinet\Catalog\Model\Product;
use Seahinet\Catalog\Model\Collection\ViewedProduct as ViewedCollection;
use Seahinet\Catalog\Model\ViewedProduct;
use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Session\Segment;

class ProductController extends ActionController
{

    use \Seahinet\Catalog\Traits\Breadcrumb;

    public function indexAction()
    {
        if ($this->getOption('product_id')) {
            $product = new Product;
            $product->load($this->getOption('product_id'));
            if ($product->getId()) {
                $root = $this->getLayout('catalog_product');
                $root->getChild('head')->setTitle($product->offsetGet('meta_title')? : $product->offsetGet('name'))
                        ->setDescription($product->offsetGet('meta_description'))
                        ->setKeywords($product->offsetGet('meta_keywords'));
                $root->getChild('product', true)->setProduct($product);
                $breadcrumb = $root->getChild('breadcrumb', true);
                $this->generateCrumbs($breadcrumb, $this->getOption('category_id'));
                $breadcrumb->addCrumb([
                    'label' => $product->offsetGet('name')
                ]);
                if (!$this->getRequest()->getHeader('DNT')) {
                    $cookie = $this->getRequest()->getCookie('viewed_product');
                    if (!in_array($this->getOption('product_id'), explode(',', $cookie))) {
                        $this->getResponse()->withCookie('viewed_product', ['value' => $this->getOption('product_id') . ',' . $cookie, 'path' => '/', 'expires' => time() + 31536000]);
                        $segment = new Segment('customer');
                        if ($segment->get('hasLoggedIn')) {
                            $collection = new ViewedCollection;
                            $collection->where(['product_id' => $this->getOption('product_id'), 'customer_id' => $segment->get('customer')->getId()]);
                            $model = new ViewedProduct;
                            $model->setData([
                                'id' => count($collection) ? $collection[0]['id'] : null,
                                'customer_id' => $segment->get('customer')->getId(),
                                'product_id' => $this->getOption('product_id')
                            ])->save();
                        }
                    } else {
                        $value = str_replace(',' . $this->getOption('product_id') . ',', ',', $this->getOption('product_id') . ',' . $cookie);
                        $this->getResponse()->withCookie('viewed_product', ['value' => $value, 'path' => '/', 'expires' => time() + 31536000]);
                    }
                }
                return $root;
            }
        }
        return $this->notFoundAction();
    }

}
