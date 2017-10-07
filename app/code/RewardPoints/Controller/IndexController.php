<?php

namespace Seahinet\RewardPoints\Controller;

use Exception;
use Seahinet\Lib\Controller\ActionController;
use Seahinet\Sales\Model\Cart;

class IndexController extends ActionController
{

    public function applyAction()
    {
        try {
            $cart = Cart::instance();
            $this->getContainer()->get('eventDispatcher')->trigger('rewardpoints.apply', ['model' => $cart]);
            $cart->collateTotals();
            $result = ['error' => 0, 'message' => []];
        } catch (Exception $e) {
            $this->getContainer()->get('log')->logException($e);
            $result = ['error' => 1, 'message' => [['message' => 'An error detected while saving. Please contact us or try again later.', 'level' => 'danger']]];
        }
        return $result;
    }

    public function cancelAction()
    {
        try {
            $cart = Cart::instance();
            $this->getContainer()->get('eventDispatcher')->trigger('rewardpoints.cancel', ['model' => $cart]);
            $cart->collateTotals();
            $result = ['error' => 0, 'message' => []];
        } catch (Exception $e) {
            $this->getContainer()->get('log')->logException($e);
            $result = ['error' => 1, 'message' => [['message' => 'An error detected while saving. Please contact us or try again later.', 'level' => 'danger']]];
        }
        return $result;
    }

    public function loadAction()
    {
        return $this->getLayout('checkout_order_rewardpoints');
    }

}
