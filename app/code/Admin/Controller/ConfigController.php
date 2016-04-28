<?php

namespace Seahinet\Admin\Controller;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Model\Config as Model;

class ConfigController extends AuthActionController
{

    use \Seahinet\Lib\Traits\DB;
    
    protected $key = null;
    protected $config = null;

    public function __call($name, $arguments)
    {
        $config = $this->getContainer()->get('config')['system'];
        $key = strtolower(substr($name, 0, -6));
        if (isset($config[$key])) {
            $this->key = $key;
            $this->config = $config[$key];
            return $this->indexAction();
        } else {
            return $this->notFoundAction();
        }
    }

    public function indexAction()
    {
        if (is_null($this->key)) {
            return $this->notFoundAction();
        }
        $root = $this->getLayout('admin_config');
        $content = $root->getChild('content');
        $content->getChild('edit')->setKey($this->key)->setElements($this->config['children']);
        $content->getChild('breadcrumb')->addCrumb(['link' => ':ADMIN/config/' . $this->key . '/', 'label' => $this->translate('System Configuration') . ' > ' . $this->translate($this->config['label'])]);
        return $root;
    }

    public function saveAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data);
            if ($result['error'] === 0) {
                $key = $data['key'];
                try {
                    $this->beginTransaction();
                    foreach ($data as $path => $value) {
                        if ($path !== 'key' && $path !== 'csrf') {
                            $model = new Model;
                            $model->load($key . '/' . $path, 'path');
                            $model->offsetSet('value', $value);
                            $model->save();
                        }
                    }
                    $this->commit();
                    $result['message'][] = ['message' => $this->translate('An item has been saved successfully.'), 'level' => 'success'];
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $this->rollback();
                    $result['message'][] = ['message' => $this->translate('An error detected while saving. Please check the log report or try again.'), 'level' => 'danger'];
                    $result['error'] = 1;
                }
            }
        }
        return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'));
    }

}
