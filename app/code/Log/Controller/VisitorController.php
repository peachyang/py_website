<?php

namespace Seahinet\Log\Controller;

use Error;
use Exception;
use Seahinet\Lib\Controller\ActionController;
use Seahinet\Log\Model\Visitor as Model;

class VisitorController extends ActionController
{

    public function indexAction()
    {
        try {
            list($customerId, $storeId, $productId) = explode('-', base64_decode($this->getOption('file')));
            $request = $this->getRequest();
            $model = new Model;
            $model->setData([
                'customer_id' => $customerId === 'n' ? null : $customerId,
                'store_id' => $storeId === 'n' ? null : $storeId,
                'product_id' => $productId === 'n' ? null : $productId,
                'session_id' => $this->getContainer()->get('session')->getId(),
                'http_referer' => $request->getHeader('HTTP_REFERER'),
                'http_user_agent' => $request->getHeader('HTTP_USER_AGENT'),
                'http_accept_charset' => $request->getHeader('HTTP_ACCEPT_CHARSET'),
                'http_accept_language' => $request->getHeader('HTTP_ACCEPT_LANGUAGE')
            ])->save();
        } catch (Error $e) {
            $this->getContainer()->get('log')->logError($e);
        } catch (Exception $e) {
            $this->getContainer()->get('log')->logException($e);
        }
        return $this->getResponse()->withHeader('Content-Type', 'application/javascript');
    }

}
