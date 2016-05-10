<?php

namespace Seahinet\Admin\Controller\I18n;

use Exception;
use Seahinet\I18n\Model\Currency as Model;
use Seahinet\Lib\Controller\AuthActionController;

class CurrencyController extends AuthActionController
{

    use \Seahinet\Lib\Traits\DB,
        \Seahinet\I18n\Traits\Currency;

    public function indexAction()
    {
        $root = $this->getLayout('admin_currency_list');
        return $root;
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_currency_edit');
        if ($id = $this->getRequest()->getQuery('id')) {
            $model = new Model;
            $model->load($id);
            $root->getChild('edit', true)->setVariable('model', $model);
        }
        return $root;
    }

    public function saveAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data);
            if ($result['error'] === 0) {
                $model = new Model;
                $model->setData('id', $data['id'])
                        ->setData('symbol', $data['symbol'])
                        ->setData('rate', $data['rate']);
                try {
                    $model->save();
                    $result['message'][] = ['message' => $this->translate('An item has been saved successfully.'), 'level' => 'success'];
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $result['message'][] = ['message' => $this->translate('An error detected while saving. Please check the log report or try again.'), 'level' => 'danger'];
                    $result['error'] = 1;
                }
            }
        }
        return $this->response($result, ':ADMIN/i18n_currency/');
    }

    public function syncAction()
    {
        $code = $this->getRequest()->getQuery('code', null);
        $config = $this->getContainer()->get('config');
        $base = $config['i18n/currency/base'];
        $collection = is_null($code) ? $config['i18n/currency/enabled'] : (array) $code;
        if (is_string($collection)) {
            $collection = explode(',', $collection);
        }
        return $this->response($this->sync($collection, $base), ':ADMIN/i18n_currency/');
    }

}
