<?php

namespace Seahinet\Cms\ViewModel;

use Seahinet\Lib\Bootstrap;
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

    public function setBlockModel(BlockModel $blockModel)
    {
        $this->blockModel = $blockModel;
        $this->cacheKey = $blockModel['code'];
        return $this;
    }

    public function setBlockId($id)
    {
        $this->blockModel = new BlockModel;
        $this->blockModel->load($id, 'code');
        $this->cacheKey = $id;
        return $this;
    }

    public function render()
    {
        if (!is_null($this->blockModel)) {
            $lang = Bootstrap::getLanguage()['code'];
            $cache = $this->getContainer()->get('cache');
            $key = $lang . '_CMS_BLOCK_' . $this->blockModel['code'];
            $rendered = $cache->fetch($key, 'VIEWMODEL_RENDERED_');
            if ($rendered) {
                return $rendered;
            }
            $rendered = $this->blockModel['content'];
            $cache->save($key, $rendered, 'VIEWMODEL_RENDERED_');
            return $rendered;
        }
        return '';
    }

}
