<?php

namespace Seahinet\Cms\ViewModel;

use Seahinet\Lib\Bootstrap;
use Seahinet\Cms\Model\Page as PageModel;
use Seahinet\Lib\ViewModel\AbstractViewModel;

class Page extends AbstractViewModel
{

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

    public function render()
    {
        if (!is_null($this->pageModel)) {
            $lang = Bootstrap::getLanguage()['code'];
            $cache = $this->getContainer()->get('cache');
            $key = $lang . '_CMS_PAGE_' . $this->pageModel['uri_key'];
            $rendered = $cache->fetch($key, 'VIEWMODEL_RENDERED_');
            if ($rendered) {
                return $rendered;
            }
            $rendered = $this->pageModel['content'];
            $cache->save($key, $rendered, 'VIEWMODEL_RENDERED_');
            return $rendered;
        }
        return '';
    }

}
