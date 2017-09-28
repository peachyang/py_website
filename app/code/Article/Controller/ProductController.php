<?php

namespace Seahinet\Article\Controller;

use Exception;
use Seahinet\Article\Model\Product;
use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Session\Segment;
use Seahinet\Customer\Model\Media;
use Seahinet\Log\Model\SocialMedia as Log;

class ProductController extends ActionController
{

    use \Seahinet\Article\Traits\Breadcrumb;

    public function indexAction()
    {
        if ($this->getOption('article_id')) {
            $product = new Product;
            $product->load($this->getOption('article_id'));
            if ($product->getId()) {
                if ($this->getOption('is_json')) {
                    return $product->toArray();
                } else {
                    (new Segment('article'))->set('article_id', $product->getId());
                    $root = $this->getLayout('article_product');
                    $root->getChild('head')->setTitle($product->offsetGet('meta_title') ?: $product->offsetGet('name'))
                            ->setDescription($product->offsetGet('meta_description'))
                            ->setKeywords($product->offsetGet('meta_keywords'));
                    $root->getChild('article', true)->setProduct($product);
                    $breadcrumb = $root->getChild('breadcrumb', true);
                    $this->generateCrumbs($breadcrumb, $this->getOption('category_id'));
                    $breadcrumb->addCrumb([
                        'label' => $product->offsetGet('name')
                    ]);
                    return $root;
                }
            }
        }
        return $this->notFoundAction();
    }

    public function shareAction()
    {
        $data = $this->getRequest()->getQuery();
        if (isset($data['media_id']) && $url = $this->getRequest()->getHeader('HTTP_REFERER')) {
            $media = new Media;
            $media->load($data['media_id']);
            $segment = new Segment('customer');
            if ($segment->get('hasLoggedIn') && !empty($data['article_id'])) {
                try {
                    $model = new Log;
                    $model->setData($data + ['customer_id' => $segment->get('customer')->getId()])->save();
                } catch (Exception $e) {
                    if (strpos($e->getMessage(), 'Duplicate') === false) {
                        $this->getContainer()->get('log')->logException($e);
                    }
                }
                $url .= '?referer=' . $segment->get('customer')->offsetGet('increment_id');
            }
            return $this->redirect($media->getUrl(['{url}' => rawurlencode($url)], $data['article_id'] ?? 0));
        }
        return $this->redirectReferer();
    }

}
