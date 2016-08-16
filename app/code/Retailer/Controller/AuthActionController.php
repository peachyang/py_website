<?php

namespace Seahinet\Retailer\Controller;

use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Session\Segment;
use Seahinet\Retailer\Model\Retailer;

abstract class AuthActionController extends ActionController
{

    public function dispatch($request = null, $routeMatch = null)
    {
        $options = $routeMatch->getOptions();
        $action = isset($options['action']) ? strtolower($options['action']) : 'index';
        $session = new Segment('customer');
        if (!$session->get('hasLoggedIn')) {
            return $this->redirect('customer/account/login/');
        } else {
            $model = new Retailer;
            $model->load($session->get('customer')->getId(), 'customer_id');
            if (in_array($action, ['apply', 'processing'])) {
                if($model->offsetGet('status') && $model->offsetGet('store_id')){
                    return $this->redirect('retailer/account/');
                }
            } else if (!$model->getId()) {
                return $this->redirect('retailer/account/apply/');
            } else if (!$model->offsetGet('status') || !$model->offsetGet('store_id')) {
                return $this->redirect('retailer/account/processing/');
            }
        }
        return parent::dispatch($request, $routeMatch);
    }

}
