<?php

namespace Seahinet\Admin\Controller\Customer;

use Exception;
use Seahinet\Admin\ViewModel\Customer\Edit\Address;
use Seahinet\Customer\Model\Collection\Customer as Collection;
use Seahinet\Customer\Model\Customer as Model;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Model\Collection\Eav\Attribute;
use Seahinet\Lib\Model\Eav\Type;
use Seahinet\Lib\Session\Segment;
use Zend\Db\Sql\Where;
use Zend\Math\Rand;

class ManageController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_customer_list');
        return $root;
    }

    public function editAction()
    {
        $query = $this->getRequest()->getQuery();
        $root = $this->getLayout(!isset($query['id']) && !isset($query['attribute_set']) ? 'admin_customer_beforeedit' : 'admin_customer_edit');
        $model = new Model;
        if (isset($query['id'])) {
            $model->load($query['id']);
            $root->getChild('head')->setTitle('Edit Customer / Customer Management');
            $root->getChild('tabs', true)->addTab('address-book', 'Address Book', 100)->addChild('address-book', (new Address)->setTemplate('admin/customer/addressList'));
            $root->getChild('extra')->addChild('address-form', (new Address)->setTemplate('admin/customer/addressForm'));
        } else {
            $root->getChild('head')->setTitle('Add New Customer / Customer Management');
        }
        $root->getChild('edit', true)->setVariable('model', $model);
        return $root;
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Customer\\Model\\Customer', ':ADMIN/customer_manage/');
    }

    public function saveAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $attributes = new Attribute;
            $attributes->withSet()->where([
                        '(is_required=1 OR is_unique=1)',
                        'attribute_set_id' => $data['attribute_set_id']
                    ])->columns(['code'])
                    ->join('eav_entity_type', 'eav_attribute.type_id=eav_entity_type.id', [], 'right')
                    ->where(['eav_entity_type.code' => Model::ENTITY_TYPE])
            ->where->notEqualTo('input', 'password');
            $required = ['store_id', 'language_id', 'attribute_set_id'];
            $unique = [];
            $attributes->walk(function ($attribute) use (&$required, &$unique) {
                if ($attribute['is_required']) {
                    $required[] = $attribute['code'];
                }
                if ($attribute['is_unique']) {
                    $unique[] = $attribute['code'];
                }
            });
            if ($unique) {
                $collection = new Collection;
                $collection->columns($unique);
                $where = new Where;
                $flag = false;
                foreach ($unique as $code) {
                    if (isset($data[$code])) {
                        $predicate = new Where;
                        $predicate->equalTo($code, $data[$code]);
                        $where->orPredicate($predicate);
                        $flag = true;
                    }
                }
                if (!empty($data['id'])) {
                    $collection->getSelect()->where->notEqualTo('id', $data['id']);
                }
                $collection->getSelect()->where->andPredicate($where);
                if ($flag && count($collection)) {
                    foreach ($collection as $item) {
                        foreach ($unique as $code) {
                            if (isset($item[$code]) && $item[$code]) {
                                $result['error'] = 1;
                                $result['message'][] = ['message' => $this->translate('The field %s has been used.', [$code]), 'level' => 'danger'];
                            }
                        }
                        break;
                    }
                }
            }
            $result = $this->validateForm($data, $required);
            if ($result['error'] === 0) {
                if (!empty($data['generate_password'])) {
                    $data['password'] = Rand::getString(8);
                    $data['modified_password'] = 1;
                } else if (empty($data['password'])) {
                    unset($data['password']);
                } else {
                    $data['modified_password'] = 1;
                }
                $files = $this->getRequest()->getUploadedFile();
                foreach ($files as $key => $file) {
                    if ($file->getError() == 0) {
                        $data[$key] = base64_encode($file->getStream()->getContents());
                    }
                }
                $model = new Model($data['language_id']);
                if (empty($data['id'])) {
                    $model->setId(null);
                    unset($data['id']);
                } else {
                    $model->load($data['id']);
                }
                $model->setData($data);
                if (empty($data['type_id'])) {
                    $type = new Type;
                    $type->load(Model::ENTITY_TYPE, 'code');
                    $model->setData('type_id', $type->getId());
                }
                try {
                    $user = (new Segment('admin'))->get('user');
                    if ($user->getStore()) {
                        if ($model->getId() && $model->offsetGet('store_id') != $user->getStore()->getId()) {
                            throw new \Exception('Not allowed to save.');
                        }
                        $model->setData('store_id', $user->getStore()->getId());
                    }
                    $this->getContainer()->get('eventDispatcher')->trigger('backend.customer.save.before', ['model' => $model, 'data' => $data]);
                    $model->save();
                    $this->getContainer()->get('eventDispatcher')->trigger('backend.customer.save.after', ['model' => $model, 'data' => $data]);
                    $result['message'][] = ['message' => $this->translate('An item has been saved successfully.'), 'level' => 'success'];
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $result['message'][] = ['message' => $this->translate('An error detected while saving. Please check the log report or try again.'), 'level' => 'danger'];
                    $result['error'] = 1;
                }
            }
        }
        return $this->response($result, ':ADMIN/customer_manage/');
    }

}
