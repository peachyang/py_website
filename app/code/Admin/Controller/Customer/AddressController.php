<?php

namespace Seahinet\Admin\Controller\Customer;

use Seahinet\Customer\Model\Address;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Model\Eav\Attribute as Model;
use Seahinet\Lib\Model\Eav\Type;
use Seahinet\Lib\Model\Eav\Attribute\Group;
use Seahinet\Lib\Model\Eav\Attribute\Set;
use Zend\Db\TableGateway\TableGateway;

class AddressController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_address_attribute_list');
        return $root;
    }

    public function editAction()
    {
        $root = $this->getLayout('admin_address_attribute_edit');
        $model = new Model;
        if ($id = $this->getRequest()->getQuery('id')) {
            $model->load($id);
            $root->getChild('head')->setTitle('Edit Address Attribute / Customer Management');
        } else {
            $root->getChild('head')->setTitle('Add New Address Attribute / Customer Management');
        }
        $root->getChild('edit', true)->setVariable('model', $model);
        $root->getChild('label', true)->setVariable('model', $model);
        return $root;
    }

    public function deleteAction()
    {
        return $this->doDelete('\\Seahinet\\Lib\\Model\\Eav\\Attribute', ':ADMIN/customer_address/');
    }

    public function saveAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data);
            if ($result['error'] === 0) {
                $model = new Model($data);
                $adapter = $this->getContainer()->get('dbAdapter');
                $connection = $adapter->getDriver()->getConnection();
                try {
                    $connection->beginTransaction();
                    $type = new Type;
                    $type->load(Address::ENTITY_TYPE, 'code');
                    if (!isset($data['id']) || (int) $data['id'] === 0) {
                        $model->setId(null);
                    }
                    $model->setData('type_id', $type->getId());
                    $model->save();
                    if (!isset($data['id']) || (int) $data['id'] === 0) {
                        $set = new Set;
                        $set->load($type->getId(), 'type_id');
                        $group = new Group;
                        $group->load($type->getId(), 'type_id');
                        $tableGateway = new TableGateway('eav_entity_attribute', $adapter);
                        $tableGateway->insert([
                            'attribute_set_id' => $set->getId(),
                            'attribute_group_id' => $group->getId(),
                            'attribute_id' => $model->getId(),
                            'sort_order' => 0
                        ]);
                    }
                    $connection->commit();
                    $result['data'] = $model->getArrayCopy();
                    $result['message'][] = ['message' => $this->translate('An item has been saved successfully.'), 'level' => 'success'];
                } catch (Exception $e) {
                    $connection->rollback();
                    $this->getContainer()->get('log')->logException($e);
                    $result['message'][] = ['message' => $this->translate('An error detected while saving. Please check the log report or try again.'), 'level' => 'danger'];
                    $result['error'] = 1;
                }
            }
        }
        return $this->response(isset($result) ? $result : ['error' => 0, 'message' => []], ':ADMIN/customer_address/');
    }

}
