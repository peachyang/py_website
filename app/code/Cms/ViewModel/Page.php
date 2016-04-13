<?php

namespace Seahinet\Cms\ViewModel;

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
        return is_null($this->pageModel) ? '' : $this->pageModel['content'];
    }

}
