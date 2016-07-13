<?php
namespace Seahinet\Api\Controller;

use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Session\Segment;
use Seahinet\Api\Model\Soap\User as Model;

/**
 * Description of UserController
 *
 * @author lenovo
 */
class UserController extends AuthActionController {
    
    public function indexAction() 
    {
        $root = $this->getLayout('api_soap_user');
        $segment = new Segment('Api');
        $root->getChild('edit', true)->setVariable('model', $segment->get('user'));
        return $root;
    }
    public function logoutAction()
    {
        $segment = new Segment('admin');
        $segment->set('isLoggedin', false);
        $segment->offsetUnset('user');
        return $this->redirect(':ADMIN');
    }
    public function listAction() 
    {
        return $this->getLayout('api_soap_user_list');
    }
    public function editAction()
    {
        $root = $this->getLayout('api_soap_user_edit');
        if ($id = $this->getRequest()->getQuery('id')) {
            $model = new Model;
            $model->load($id);
            $root->getChild('edit', true)->setVariable('model', $model);
            $root->getChild('head')->setTitle('Edit SOAP User / SOAP/RPC - User');
        } else {
            $root->getChild('head')->setTitle('Add New SOAP User / SOAP/RPC - User');
        }
        return $root;
    }
    public function deleteAction() 
    {
        return $this->doDelete('\\Seahinet\\Api\\Model\\User', ':ADMIN/user/list/');
    }
    public function saveAction()
    {
        $result = ['error'=>0, 'message'=>[]];
        if($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $segment = new Segment('admin');
            $user = $segment->get('user');
            $result = $this->validateForm($data, ['username' ,'password']);
            if(empty($data['cpassword']) || empty($data['passwored']) || $data['cpassword'] !== $data['password']) {
                $result['message'][] = ['message'=> $this->translate('The confirm password is not equal to the passwor.d'),'level'=> 'danger'];
                $result['error'] = 1;
            }else if($result['error'] === 0) {
                $moder = new Model($data);
                if(isset($data['id']) || (int) $data['id'] === 0) {
                    $model->setId(NULL);
                }
                try {
                    $model->save();
                    if(isset($data['id']) && $data['id']==$user->getId()) {
                        $user->setDate($data);
                        $segment->set('user', clone $user);
                    }
                    $result['message'][] = ['message'=>$this->translate('An item has been saved successfully.'),'level'=>'success'];
                } catch (Exception $ex) {
                    $this->getContainer()->get('log')->logException($ex);
                    $result['message'][] = ['message'=>$this->translate('An error detected while saving. Please check the log report or try again.'), 'leve'=>'danger'];
                    $result['error']=1;
                }
        }
    }
    $referer = $this->getRequest()->getHeader('HTTP_REFERER');
    return $this->response($result, strpos($referer, 'edit')? ':ADMIN/api_soap_user/list/': ':ADMIN/api_soap_user/');
    }
}
