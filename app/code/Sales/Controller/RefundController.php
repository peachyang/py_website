<?php
namespace Seahinet\Sales\Controller;

use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\Collection\Order;
use Seahinet\Sales\Model\Rma;

class RefundController extends ActionController
{

    public function indexAction(){
        $segment = new Segment('customer');
        $customer = $segment->get('customer');
        if ($customer){
            $customerId = $customer->getId();
            echo $customerId;die;
            $orders = new Order;
            $orders->where(['customer_id' => $customerId]);
            foreach ($orders as $key => $order){
                if(!$order->canRefund() && !$order->canHold()){
                    unset($orders[$key]);
                }
            }
            $root = $this->getLayout('sales_refund');
            $root->getChild('main', true)->setVariable('orders', $orders);
        }else {
            $root = $this->getLayout('sales_refund');
        }
        return $root;
    }
    
    public function saveRefundAction(){
        if ($this->getRequest()->isPost()){
            $data = $this->getRequest()->getPost();
            $refund = new Rma;
            $refund->setData($data);
            if ($refund->save()){
                $result = ['error'=>0,'message'=>['message'=>'Submitted Successfully']];
            }else {
                $result = ['error'=>1,'message'=>['message'=>'An error detected. Please contact us or try again later.']];
            }
        }
        return $this->response(isset($result) ? $result : ['error' => 0, 'message' => []], isset($url) ? $url : 'customer/account/', 'customer');
    }
}
