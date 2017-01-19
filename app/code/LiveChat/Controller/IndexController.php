<?php

namespace Seahinet\LiveChat\Controller;

use Exception;
use Seahinet\Customer\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;
use Seahinet\LiveChat\Model\Collection\Session as Collection;
use Seahinet\LiveChat\Model\Session as Model;
use Zend\Math\Rand;

class IndexController extends AuthActionController
{

    use \Seahinet\Lib\Traits\DB;

    public function indexAction()
    {
        $segment = new Segment('customer');
        $from = $segment->get('customer')->getId();
        if (($to = $this->getRequest()->getQuery('chat')) && ($from != $to)) {
            if (substr($to, 0, 1) === 'g') {
                $error = $this->inGroup($from, $to);
            } else {
                $error = $this->withSingle($from, $to);
            }
        } else {
            $error = $this->translate('Invalid chat id');
        }
        $root = $this->getLayout('livechat');
        if ($error) {
            $root->getChild('messages', true)->addMessage($error, 'danger');
        } else {
            $root->getChild('livechat', true)->setVariable('current', $id);
        }
        return $root;
    }

    protected function withSingle($from, $to)
    {
        $collection = new Collection;
        $select = clone $collection->getSelect();
        $select->columns(['id'])
                ->where(['customer_id' => $to]);
        $collection->columns(['id'])
                ->where(['customer_id' => $from])
        ->where->in('id', $select);
        $collection->load(true, true);
        $error = '';
        if (count($collection)) {
            $id = $collection[0]['id'];
        } else if ($this->canChat($from, $to)) {
            while (1) {
                $id = Rand::getString(Rand::getInteger(32, 40), 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
                $model = new Model;
                $model->load($id);
                if (!$model->getId()) {
                    try {
                        $this->beginTransaction();
                        $model->setData([
                            'id' => $id,
                            'customer_id' => $from
                        ])->save([], true);
                        $model = new Model;
                        $model->setData([
                            'id' => $id,
                            'customer_id' => $to
                        ])->save([], true);
                        $this->commit();
                    } catch (Exception $e) {
                        $this->rollback();
                        $error = $this->translate('Invalid chat id');
                    }
                    break;
                }
            }
        } else {
            $error = $this->translate('Invalid chat id');
        }
        return $error;
    }

    protected function inGroup($from, $to)
    {
        
    }

    protected function canChat($from, $to)
    {
        return true;
    }

}
