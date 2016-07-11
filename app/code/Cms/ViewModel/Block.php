<?php

namespace Seahinet\Cms\ViewModel;

use Seahinet\Lib\Bootstrap;
use Seahinet\Cms\Model\Block as BlockModel;
use Seahinet\Cms\Model\Collection\Block as BlockCollection;
use Seahinet\Lib\ViewModel\AbstractViewModel;

class Block extends AbstractViewModel
{

    use \Seahinet\Cms\Traits\Renderer;

    /**
     * @var int
     */
    protected $blockId = null;

    /**
     * @var BlockModel
     */
    protected $blockModel = null;

    public function getBlockModel()
    {
        if (is_null($this->blockModel) && !is_null($this->blockId)) {
            $this->blockModel = new BlockModel;
            $this->blockModel->load($this->blockId, 'code');
        }
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
        $collection = new BlockCollection;
        $collection->where(['cms_block.code' => $id, 'language_id' => Bootstrap::getLanguage()->getId()]);
        if (count($collection)) {
            $this->blockId = $id;
            $this->blockModel = new BlockModel($collection[0]);
            $this->cacheKey = $id;
        }
        return $this;
    }

    public function render()
    {
        if (!is_null($this->getBlockModel()) && $this->getBlockModel()->getId()) {
            $lang = Bootstrap::getLanguage()['code'];
            $cache = $this->getContainer()->get('cache');
            $key = $lang . '_CMS_BLOCK_' . $this->getBlockModel()['code'];
            $rendered = $cache->fetch($key, 'VIEWMODEL_RENDERED_');
            if (!$rendered) {
//            $content = $this->replace($this->getBlockModel()['content'], [
//                'base_url' => $this->getBaseUrl(),
//                'pub_url' => $this->getPubUrl(),
//                'res_url' => $this->getResourceUrl()
//            ]);
                $rendered = $this->getBlockModel()['store_id'] ?
                        $this->getContainer()->get('htmlpurifier')
                                ->purify($this->getBlockModel()['content']) : $this->getBlockModel()['content'];
                $cache->save($key, $rendered, 'VIEWMODEL_RENDERED_');
            }
            return $this->replace($rendered, [
                        'base_url' => $this->getBaseUrl(),
                        'pub_url' => $this->getPubUrl(),
                        'res_url' => $this->getResourceUrl()
            ]);
        }
        return '';
    }

}
