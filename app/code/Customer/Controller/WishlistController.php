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
        $root = $this->getLayout('customer_account_wishlist');
        $root->getChild('main', true)->setVariable('collection', $collection);
        return $root;
    }

    public function addAction()
    {
        $data = $this->getRequest()->getQuery();
        $segment = new Segment('customer');
        $customerId = $segment->get('customer')->getId();
        try {
            $wishlist = new Model;
            $wishlist->load($customerId, 'customer_id');
            if (!$wishlist->getId()) {
                $wishlist->load($wishlist->getId())->setData(['customer_id' => $customerId, 'id' => null])->save();
            }
            $data['wishlist_id'] = $wishlist->getId();
            $wishlist->getId();
            $wishlist->addItem($data);
            $result['message'][] = ['message' => $this->translate('success'), 'level' => 'success'];
        } catch (\Exception $e) {
            $result['message'][] = ['message' => $this->translate('failed'), 'level' => 'danger'];
            $this->getContainer()->get('log')->logException($e);
        }
        return $this->redirect('customer/wishlist/');
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
        return $this->redirect('customer/wishlist/');
    }

}
