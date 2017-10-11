<?php

namespace Seahinet\Cms\Controller;

use Seahinet\Lib\Controller\ActionController;
use Seahinet\Cms\Model\Page;
use Seahinet\Lib\Session\Segment;

class PageController extends ActionController
{

    use \Seahinet\Cms\Traits\Breadcrumb,
        \Seahinet\Lib\Traits\DB,
        \Seahinet\Lib\Traits\Filter;

    public function indexAction()
    {
        $page = $this->getOption('page');
        if (!$page) {
            return $this->notFoundAction();
        }
        $category = $this->getOption('category');
        $layout = $this->getContainer()->get('layout');
        $root = $layout->getLayout('page-' . $page['uri_key'], true) ?:
                $layout->getLayout($category && $category->offsetGet('show_navigation') ? 'cms_page_with_nav' : 'cms_page', true);
        $root->addBodyClass('page-' . $page['uri_key']);
        $head = $root->getChild('head');
        $head->setTitle($page['title'])
                ->setKeywords($page['keywords'])
                ->setDescription($page['description']);
        $navigation = $root->getChild('navigation', true);
        if ($navigation) {
            $navigation->setVariables([
                'page' => $page,
                'category' => $category
            ]);
        }
        $root->getChild('page', true)->setPageModel($page);
        return $root;
    }

    public function homeAction()
    {
        $root = $this->getLayout('page_home');
        return $root;
    }

    public function pageAction()
    {
        $page = $this->getOption('page');
        if (!$page) {
            return $this->notFoundAction();
        } else {
            if ($page->getId()) {
                if ($this->getOption('is_json')) {
                    return $page->toArray();
                } else {
                    $category = $this->getOption('category');
                    $root = $this->getLayout('page_view');
                    $root->getChild('head')->setTitle($page->offsetGet('meta_title') ?: $page->offsetGet('title'))
                            ->setDescription($page->offsetGet('meta_description'))
                            ->setKeywords($page->offsetGet('meta_keywords'));
                    $root->getChild('page', true)->setPageModel($page);
                    $breadcrumb = $root->getChild('breadcrumb', true);
                    $this->generateCrumbs($breadcrumb, $this->getOption('category_id'));
                    $breadcrumb->addCrumb([
                        'label' => $page->offsetGet('title')
                    ]);
                    return $root;
                }
            }
        }
        return $this->notFoundAction();
    }

}
