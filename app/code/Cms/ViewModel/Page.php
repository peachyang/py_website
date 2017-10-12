<?php

namespace Seahinet\Cms\ViewModel;

use Exception;
use Seahinet\Lib\Bootstrap;
use Seahinet\Cms\Model\Page as PageModel;
use Seahinet\Lib\ViewModel\AbstractViewModel;
use Seahinet\Lib\ViewModel\Template;

class Page extends Template
{

    use \Seahinet\Cms\Traits\Renderer;

    /**
     * @var PageModel
     */
    protected $pageModel = null;

    public function getPageModel()
    {
        return $this->pageModel;
    }

    public function setPageModel(PageModel $pageModel)
    {
        $this->pageModel = $pageModel;
        return $this;
    }

//    public function render()
//    {
//        if (!is_null($this->pageModel)) {
//            try {
//                $lang = Bootstrap::getLanguage()['code'];
//                $cache = $this->getContainer()->get('cache');
//                $key = $lang . '_CMS_PAGE_' . $this->pageModel['uri_key'];
//                $rendered = $cache->fetch($key, 'VIEWMODEL_RENDERED_');
//                if (!$rendered) {
//                    $rendered = $this->pageModel['store_id'] ?
//                            $this->getContainer()->get('htmlpurifier')
//                                    ->purify($this->pageModel['content']) : $this->pageModel['content'];
//                    $cache->save($key, $rendered, 'VIEWMODEL_RENDERED_');
//                }
//            } catch (Exception $e) {
//                $this->getContainer()->get('log')->logException($e);
//                $rendered = '';
//            }
//            return $rendered ? $this->replace($rendered, [
//                        'base_url' => $this->getBaseUrl(),
//                        'pub_url' => $this->getPubUrl(),
//                        'res_url' => $this->getResourceUrl()
//                    ]) : '';
//        }
//        return '';
//    }
}
