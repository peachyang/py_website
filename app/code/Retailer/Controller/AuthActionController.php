<?php

namespace Seahinet\Retailer\Controller;

use Seahinet\Lib\Controller\ActionController;
use Seahinet\Lib\Session\Segment;
use Seahinet\Retailer\Model\{
    Application,
    Retailer
};

abstract class AuthActionController extends ActionController
{

    use \Seahinet\Lib\Traits\DB;

    protected $retailer = null;

    public function dispatch($request = null, $routeMatch = null)
    {
        $options = $routeMatch->getOptions();
        $action = isset($options['action']) ? strtolower($options['action']) : 'index';
        $session = new Segment('customer');
        if (!$session->get('hasLoggedIn')) {
            return $this->redirect('customer/account/login/');
        } else {
            $model = new Application;
            $model->load($session->get('customer')->getId());
            if (in_array($action, ['apply', 'applypost', 'reapply', 'processing'])) {
                if ($model->offsetGet('status')) {
                    return $this->redirect('retailer/store/setting/');
                } else if ($action === 'apply' && $model->getId()) {
                    return $this->redirect('retailer/account/processing/');
                }
            } else if (!$model->getId()) {
                return $this->redirect('retailer/account/apply/');
            } else if (!$model->offsetGet('status')) {
                return $this->redirect('retailer/account/processing/');
            }
        }
        return parent::dispatch($request, $routeMatch);
    }

    protected function getRetailer()
    {
        if (is_null($this->retailer)) {
            $session = new Segment('customer');
            $this->retailer = new Retailer;
            $this->retailer->load($session->get('customer')->getId(), 'customer_id');
        }
        return $this->retailer;
    }

}
