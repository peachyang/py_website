<?php

namespace Seahinet\Admin\Controller;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Model\Collection\Eav\Type;

class IndexerController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout('admin_indexer');
    }

    public function rebuildAction()
    {
        $code = $this->getRequest()->getPost('id');
        $result = ['message' => [], 'error' => 0];
        if (!$code) {
            $code = (new Type)->toArray();
        }
        $manager = $this->getContainer()->get('indexer');
        $count = 0;
        touch(BP . 'maintence');
        try {
            foreach ((array) $code as $indexer) {
                $manager->reindex(is_string($indexer) ? $indexer : $indexer['code']);
                $count ++;
            }
        } catch (\Exception $e) {
            $this->getContainer()->get('log')->logException($e);
        }
        unlink(BP . 'maintence');
        $result['message'][] = ['message' => $this->translate('%d indexer(s) have been rebuild successfully.', [$count]), 'level' => 'success'];
        return $this->response($result, ':ADMIN/indexer/');
    }

}
