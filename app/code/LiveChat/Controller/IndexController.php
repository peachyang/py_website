<?php

namespace Seahinet\LiveChat\Controller;

use Exception;
use Seahinet\LiveChat\Exception\InvalidIdException;
use Seahinet\Customer\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;
use Seahinet\LiveChat\Model\Collection\Session as Collection;
use Seahinet\LiveChat\Model\{
    Group,
    Session as Model
};
use Zend\Math\Rand;

class IndexController extends AuthActionController
{

    use \Seahinet\Lib\Traits\DB;

    public function prepareAction()
    {
        if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isHead()) {
            $segment = new Segment('customer');
            $from = $segment->get('customer')->getId();
            $collection = new Collection;
            $collection->where(['customer_id' => $from]);
            $collection->load(true, true);
            $content = [];
            foreach ($collection as $item) {
                $content[] = $item['id'];
            }
            $fp = fopen('/tmp/livechat-' . $from, 'w');
            fwrite($fp, json_encode($content));
            fclose($fp);
        }
        exit();
    }

    public function indexAction()
    {
        $segment = new Segment('customer');
        $from = $segment->get('customer')->getId();
        $error = '';
        if (($to = $this->getRequest()->getQuery('chat')) && ($from != $to)) {
            try {
                if (substr($to, 0, 1) === 'g') {
                    $id = $this->inGroup($from, $to);
                } else {
                    $id = $this->withSingle($from, $to);
                }
            } catch (InvalidIdException $e) {
                $error = $this->translate($e->getMessage());
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
                        throw new InvalidIdException('Invalid chat id');
                    }
                    break;
                }
            }
        } else {
            throw new InvalidIdException('Invalid chat id');
        }
        return $id;
    }

    protected function inGroup($from, $to)
    {
        $group = new Group;
        $group->load(substr($to, 1));
        if (in_array($from, $group->getMembers())) {
            if ($group['session_id']) {
                return $group['session_id'];
            } else {
                while (1) {
                    $id = Rand::getString(Rand::getInteger(10, 30), 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
                    $tmpGroup = new Group;
                    $tmpGroup->load($id, 'session_id');
                    if (!$tmpGroup->getId()) {
                        $group->setData('session_id', $id)->save();
                        break;
                    }
                }
                return $id;
            }
        } else {
            throw new InvalidIdException('Invalid chat id');
        }
    }

    protected function canChat($from, $to)
    {
        return true;
    }

}
