<?php

namespace Seahinet\Customer\Controller;

use Seahinet\Customer\Model\Wishlist as Model;
use Seahinet\Lib\Session\Segment;
use Seahinet\Customer\Model\Wishlist\Item;

class WishlistController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout('customer_account_wishlist');
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
            $result['data'] = ['wishlist_id' => $wishlist->getId()];
            $data['wishlist_id'] = $wishlist->getId();
            $wishlist->getId();
            $wishlist->addItem($data);
            $result['message'][] = ['message' => $this->translate('success'), 'level' => 'success'];
        } catch (\Exception $e) {
            $result['message'][] = ['message' => $this->translate('failed'), 'level' => 'danger'];
            $this->getContainer()->get('log')->logException($e);
        }
        return $this->redirect('customer/account/wishlist/');
    }

    function commitAction()
    {
        $data = $this->getRequest()->getPost();
    }

    public function deleteAction()
    {
        if ($this->getRequest()->isDelete()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['id']);
            $item = new Item;
            if ($result['error'] === 0) {
                try {
                    $item->setId($data['id'])->remove();
                    $result['removeLine'] = 1;
                    $result['message'][] = ['message' => $this->translate('The product has been removed from wishlist successfully.'), 'level' => 'success'];
                } catch (Exception $e) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate('An error detected while deleting. Please contact us or try again later.'), 'level' => 'success'];
                }
            }
        }
        return $this->response(isset($result) ? $result : ['error' => 0, 'message' => []], 'customer/wishlist/', 'customer');
    }

}
