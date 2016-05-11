<?php

namespace Seahinet\Resource\ViewModel;

use Seahinet\Resource\Model as ResourceModel;
use Seahinet\Lib\ViewModel\AbstractViewModel;

class Resource extends AbstractViewModel
{

    /**
     * @var PageModel
     */
    protected $ResourceModel = null;

    public function getResourceModel()
    {
        return $this->ResourceModel;
    }

    public function setPageModel(ResourceModel $ResourceModel)
    {
        $this->ResourceModel = $ResourceModel;
        return $this;
    }

    public function render()
    {
        return is_null($this->ResourceModel) ? '' : $this->ResourceModel['content'];
    }

}
