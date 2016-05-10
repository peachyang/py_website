<?php

namespace Seahinet\Resources\ViewModel;

use Seahinet\Resources\Model as ResourcesModel;
use Seahinet\Lib\ViewModel\AbstractViewModel;

class Resources extends AbstractViewModel
{

    /**
     * @var PageModel
     */
    protected $resourcesModel = null;

    public function getResourcesModel()
    {
        return $this->resourcesModel;
    }

    public function setPageModel(ResourcesModel $resourcesModel)
    {
        $this->resourcesModel = $resourcesModel;
        return $this;
    }

    public function render()
    {
        return is_null($this->resourcesModel) ? '' : $this->resourcesModel['content'];
    }

}
