<?php

namespace Seahinet\I18n\Controller;

use Seahinet\I18n\Model\Currency;
use Seahinet\Lib\Controller\ActionController;

class CurrencyController extends ActionController
{

    public function indexAction()
    {
        $code = $this->getRequest()->getQuery('currency');
        $currency = new Currency;
        $currency->load($code, 'code');
        if ($currency->getId()) {
            $this->getResponse()->withCookie('currency', ['value' => $code, 'path' => '/']);
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            return ['redirect' => $this->getRequest()->getHeader('HTTP_REFERER')];
        } else {
            return $this->redirectReferer();
        }
    }

}
