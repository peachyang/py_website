<?php
namespace Seahinet\Sales\Controller;

use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Session\Segment;
use Seahinet\Sales\Model\Collection\Order;
use Seahinet\Sales\Model\Order as OrderModel;
use Seahinet\Sales\Model\Rma;

class RefundController extends ActionController
{

    public function indexAction(){
        $segment = new Segment('customer');
        $customer = $segment->get('customer');
        if ($customer){
            $customerId = $customer->getId();
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
            $order = (new OrderModel)->load($data['order_id'],isset($data['customer_id']) ? 'id' : 'increment_id');
            if (!empty($order->toArray()) && ($order->canRefund() || $order->canHold())){
                $refund = new Rma;
                $refund->setData([
                    'order_id' => $order['id'],
                    'comment' => $data['comment']
                ]);
                try {
                    $refund->save();
                    $result['error'] = 0;
                    $result['message'][] = ['message'=>'Submitted Successfully', 'level' => 'success'];
                    $url = isset($data['customer_id']) ? 'customer/account/' : '/';
                } catch (Swift_TransportException $e) {
                    $this->getContainer()->get('log')->logException($e);
                } catch (Exception $e) {
                    $result['error'] = 1;
                    $result['message'][] = ['message'=>'An error detected. Please contact us or try again later.', 'level' => 'danger'];
                }
            }else {
                $result['error'] = 1;
                $result['message'][] = ['message'=>'Invalid order ID', 'level' => 'danger'];
            }
        }
        return $this->response(isset($result) ? $result : ['error' => 0, 'message' => []], isset($url) ? $url : 'customer/account/', 'customer');
    }
}
