<?php

namespace Seahinet\Resource\ViewModel;

use Seahinet\Resource\Model\Resource as ResourceModel;
use Seahinet\Lib\ViewModel\AbstractViewModel;

class Resource extends AbstractViewModel
{

    protected $resourceModel = null;

    public function getResourceModel()
    {
        return $this->resourceModel;
    }

    public function setResourceModel(ResourceModel $ResourceModel)
    {
        $this->resourceModel = $ResourceModel;
        return $this;
    }

    public function render()
    {
        return is_null($this->ResourceModel) ? '' : $this->ResourceModel['content'];
    }

}
