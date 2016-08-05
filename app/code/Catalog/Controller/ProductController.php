<?php

namespace Seahinet\Catalog\Controller;

use Seahinet\Catalog\Model\Category;
use Seahinet\Catalog\Model\Product;
use Seahinet\Catalog\Model\Collection\Logview;
use Seahinet\Catalog\Model\Logview as LogviewModel;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Controller\ActionController;

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
                    $log_view = $this->getRequest()->getCookie('log_view');
                    if (!in_array($this->getOption('product_id'), explode(',', $log_view))) {
                        $this->getResponse()->withCookie('log_view', ['value' => $this->getOption('product_id') . ',' . $log_view, 'path' => '/', 'expires' => time() + 3600 * 24 * 365 * 5]);
                    } else {
                        $newLogView = str_replace(',' . $this->getOption('product_id') . ',', ',', $this->getOption('product_id') . ',' . $log_view);
                        $this->getResponse()->withCookie('log_view', ['value' => $newLogView, 'path' => '/', 'expires' => time() + 3600 * 24 * 365 * 5]);
                    }
                    $segment = new Segment('customer');
                    if ($segment->get('hasLoggedIn')) {
                        $logView = new Logview();
                        $logView->where(['product_id' => $this->getOption('product_id'), 'customer_id' => $segment->get('customer')->getId()]);
                        if (!$logView[0]) {
                            $logViewModel = new LogviewModel();
                            $logViewModel->setData([
                                'customer_id' => $segment->get('customer')->getId(),
                                'product_id' => $this->getOption('product_id'),
                            ])->save();
                        }
                    }
                }
                return $root;
            }
        }
        return $this->notFoundAction();
    }

}
