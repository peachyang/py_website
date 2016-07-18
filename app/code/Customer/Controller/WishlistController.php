<?php

namespace Seahinet\Customer\Controller;

use Seahinet\Customer\Model\Collection\Wishlist as Collection;
use Seahinet\Customer\Model\Wishlist as Model;
use Seahinet\Lib\Session\Segment;
use Seahinet\Customer\Model\Wishlist\Item;

class WishlistController extends AuthActionController
{

    public function indexAction()
    {
        $segment = new Segment('customer');
        $customerId = $segment->get('customer')->getId();
        $collection = new Collection;
        $collection->where(['customer_id' => $customerId]);
        $root = $this->getLayout('wishlist');
        $root->getChild('wishlist', true)->setVariable('collection', $collection);
        return $root;
    }

    public function addAction()
    {
        $data = $this->getRequest()->getPost();
        $result = $this->validateForm($data, ['name']);
        if ($result['error'] === 0) {
            $segment = new Segment('customer');
            $customerId = $segment->get('customer')->getId();
            try {
                $wishlist = new Model;
                $wishlist->load($customerId, 'customer_id');
                $wishlist->addItem($data);
                $result['message'][] = ['message' => $this->translate('success'), 'level' => 'success'];
            } catch (\Exception $e) {
                $result['message'][] = ['message' => $this->translate('failed'), 'level' => 'danger'];
                $this->getContainer()->get('log')->logException($e);
            }
            
            $data['wishlist_id'] = $wishlist->getId();
                    print_r($data);
            //die();
            $wishlist->getId();
            $wishlist->addItem($data);
            $result['message'][] = ['message' => $this->translate('success'), 'level' => 'success'];
        }
//        }
        return $this->redirect('customer/wishlist/');
//        return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'), 'customer');
    }

    public function commitAction()
    {
        $data = $this->getRequest()->getPost();
    }

    public function deleteAction()
    {
        $item = new Item;
        $data = $this->getRequest()->getQuery();
        $item->setData('id', $data['id'])->remove();
        
    }

}
