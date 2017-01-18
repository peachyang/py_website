<?php

namespace Seahinet\LiveChat\Controller;

use Exception;
use Seahinet\Customer\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;
use Seahinet\LiveChat\Model\Collection\Session as Collection;
use Seahinet\LiveChat\Model\Session as Model;
use Zend\Db\Sql\Where;
use Zend\Math\Rand;

class IndexController extends AuthActionController
{

    public function indexAction()
    {
        if ($to = $this->getRequest()->getQuery('chat')) {
            $segment = new Segment('customer');
            $from = $segment->get('customer')->getId();
            $collection = new Collection;
            $where1 = new Where;
            $where1->equalTo('customer_id_1', $from)
                    ->equalTo('customer_id_2', $to);
            $where2 = new Where;
            $where2->equalTo('customer_id_2', $from)
                    ->equalTo('customer_id_1', $to);
            $collection->columns(['id'])
                    ->where([$where1, $where2], 'OR');
            $collection->load(true, true);
            $error = '';
            if (count($collection)) {
                $id = $collection[0]['id'];
            } else {
                while (1) {
                    $id = Rand::getString(Rand::getInteger(32, 40), 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
                    $model = new Model;
                    $model->load($id);
                    if (!$model->getId()) {
                        try {
                            $model->setData([
                                'id' => $id,
                                'customer_id_1' => $from,
                                'customer_id_2' => $to
                            ])->save([], true);
                        } catch (Exception $e) {
                            $error = $this->translate('Invalid chat id');
                        }
                        break;
                    }
                }
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

}
