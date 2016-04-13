<?php

namespace Seahinet\Cms\ViewModel;

use Seahinet\Cms\Model\Block as BlockModel;
use Seahinet\Lib\ViewModel\AbstractViewModel;

class Block extends AbstractViewModel
{

    /**
     * @var BlockModel
     */
    protected $blockModel = null;

    public function getBlockModel()
    {
        return $this->blockModel;
    }

    public function setBlockModel(BlockModel $pageModel)
    {
        $this->blockModel = $pageModel;
        return $this;
    }

    public function setBlockId($id)
    {
        $this->blockModel = new BlockModel;
        $this->blockModel->load($id, 'code');
        return $this;
    }

    public function render()
    {
        return is_null($this->blockModel) ? '' : $this->blockModel['content'];
    }

}
