<?php

namespace Seahinet\Lib\Controller;

use Closure;
use Exception;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Route\RouteMatch;
use Seahinet\Lib\Session\Segment;

/**
 * Controller with authorization for backend request
 */
class AuthActionController extends ActionController
{

    use \Seahinet\Lib\Traits\DB;

    /**
     * {@inheritdoc}
     */
    public function dispatch($request = null, $routeMatch = null)
    {
        $this->request = $request;
        if (!$routeMatch instanceof RouteMatch) {
            $method = 'notFoundAction';
        } else {
            $method = $routeMatch->getMethod();
            $this->options = $routeMatch->getOptions();
            $segment = new Segment('admin');
            $permission = str_replace('Seahinet\\', '', preg_replace('/Controller(?:\\\\)?/', '', get_class($this))) . '::' . str_replace('Action', '', $method);
            if (!$segment->get('hasLoggedIn') || !$segment->get('user')->getRole()->hasPermission($permission)) {
                return $this->notFoundAction();
            }
            if (!is_callable([$this, $method])) {
                $method = 'notFoundAction';
            }
        }
        return $this->doDispatch($method);
    }

    /**
     * {@inheritdoc}
     */
    protected function doDispatch($method = 'notFoundAction')
    {
        if ($method !== 'notFoundAction') {
            $param = ['controller' => $this, 'method' => $method];
            $dispatcher = $this->getContainer()->get('eventDispatcher');
            $dispatcher->trigger(get_class($this) . '.dispatch.before', $param);
            $dispatcher->trigger('auth.dispatch.before', $param);
            $dispatcher->trigger('dispatch.before', $param);
        }
        $result = $this->$method();
        if ($method !== 'notFoundAction') {
            $param = ['controller' => $this, 'method' => $method, 'result' => &$result];
            $dispatcher = $this->getContainer()->get('eventDispatcher');
            $dispatcher->trigger(get_class($this) . '.dispatch.after', $param);
            $dispatcher->trigger('auth.dispatch.after', $param);
            $dispatcher->trigger('dispatch.after', $param);
        }
        return $result;
    }

    protected function doDelete($modelName, $redirect = null)
    {
        if ($this->getRequest()->isDelete()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['id']);
            if ($result['error'] === 0) {
                try {
                    $model = is_object($modelName) && $modelName instanceof AbstractModel ? $modelName : new $modelName();
                    $count = 0;
                    foreach ((array) $data['id'] as $id) {
                        $model->setId($id)->remove();
                        $count++;
                    }
                    $result['message'][] = ['message' => $this->translate('%d item(s) have been deleted successfully.', [$count]), 'level' => 'success'];
                    $result['removeLine'] = (array) $data['id'];
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $result['message'][] = ['message' => $this->translate('An error detected while deleting. Please check the log report or try again.'), 'level' => 'danger'];
                    $result['error'] = 1;
                }
            }
        }
        return $this->response(isset($result) ? $result : ['error' => 0, 'message' => []], is_null($redirect) ? $this->getRequest()->getHeader('HTTP_REFERER') : $redirect);
    }

    protected function doSave($modelName, $redirect = null, $required = [], $beforeSave = null, $transaction = false)
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, $required);
            if ($result['error'] === 0) {
                if (is_subclass_of($modelName, '\\Seahinet\\Lib\\Model\\Eav\\Entity')) {
                    $model = new $modelName(isset($data['language_id']) ? $data['language_id'] : Bootstrap::getLanguage()->getId(), $data);
                } else {
                    $model = new $modelName($data);
                }
                if (!isset($data[$model->getPrimaryKey()]) || (int) $data[$model->getPrimaryKey()] === 0) {
                    $model->setId(null);
                }
                if ($transaction) {
                    $this->beginTransaction();
                }
                if ($beforeSave instanceof Closure) {
                    $beforeSave($model, $data);
                }
                try {
                    $model->save();
                    $result['data'] = $model->getArrayCopy();
                    $result['message'][] = ['message' => $this->translate('An item has been saved successfully.'), 'level' => 'success'];
                    if ($transaction) {
                        $this->commit();
                    }
                } catch (Exception $e) {
                    if ($transaction) {
                        $this->rollback();
                    }
                    $this->getContainer()->get('log')->logException($e);
                    $result['message'][] = ['message' => $this->translate('An error detected while saving. Please check the log report or try again.'), 'level' => 'danger'];
                    $result['error'] = 1;
                }
            }
        }
        return $this->response(isset($result) ? $result : ['error' => 0, 'message' => []], is_null($redirect) ? $this->getRequest()->getHeader('HTTP_REFERER') : $redirect);
    }

}
