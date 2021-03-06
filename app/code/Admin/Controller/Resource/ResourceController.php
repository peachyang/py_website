<?php

namespace Seahinet\Admin\Controller\Resource;

use Exception;
use Seahinet\Lib\Controller\AuthActionController;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Session\Segment;
use Seahinet\Resource\Model\{
    Category,
    Resource as Model
};
use Seahinet\Resource\Model\Collection\Resource as Collection;

class ResourceController extends AuthActionController
{

    public function indexAction()
    {
        return $this->getLayout($this->getRequest()->isXmlHttpRequest() ? 'admin_resource_list' : 'admin_resource');
    }

    public function navAction()
    {
        return $this->getLayout('admin_resource_nav');
    }

    public function uploadAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $files = $this->getRequest()->getUploadedFile()['files'];
            $user = (new Segment('admin'))->get('user');
            $result = $this->validateForm($data);
            if ($result['error'] === 0) {
                try {
                    foreach ($files as $file) {
                        $name = $file->getClientFilename();
                        $model = new Model();
                        $model->moveFile($file)
                                ->setData([
                                    'store_id' => $user->offsetGet('store_id') ?: (empty($data['store_id']) ? null : $data['store_id']),
                                    'uploaded_name' => $name,
                                    'file_type' => $file->getClientMediaType(),
                                    'category_id' => empty($data['category_id']) ? null : $data['category_id']
                                ])->save();
                    }
                } catch (Exception $e) {
                    $result['error'] = 1;
                    $result['message'][] = ['message' => $this->translate($e->getMessage()), 'level' => 'danger'];
                }
            }
        }
        return $this->response($result, $this->getRequest()->getHeader('HTTP_REFERER'));
    }

    public function deleteAction()
    {
        if ($this->getRequest()->isDelete()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data);
            if (!isset($data['r'])) {
                $data['r'] = [];
            }
            if (!isset($data['f'])) {
                $data['f'] = [];
            }
            if ($result['error'] === 0) {
                $storeId = (new Segment('admin'))->get('user')->offsetGet('store_id');
                try {
                    $path = BP . Model::$options['path'];
                    foreach ((array) $data['r'] as $id) {
                        $model = new Model;
                        $model->load($id);
                        if ($model->getId() && (!$storeId || $model->offsetGet('store_id') == $storeId)) {
                            $type = $model->offsetGet('file_type');
                            $collection = new Collection;
                            $collection->where(['md5' => $model['md5']])
                            ->where->notEqualTo('id', $id);
                            if (count($collection) === 0 && file_exists($filename = $path . substr($type, 0, strpos($type, '/') + 1) . $model->offsetGet('real_name'))) {
                                unlink($filename);
                            }
                            $model->remove();
                        }
                    }
                    foreach ((array) $data['f'] as $id) {
                        $model = new Category;
                        $model->load($id);
                        if (!$storeId || $model->offsetGet('store_id') == $storeId) {
                            $model->remove();
                        }
                    }
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $result['message'][] = ['message' => $this->translate('An error detected while deleting.'), 'level' => 'danger'];
                    $result['error'] = 1;
                }
            }
        }
        return $this->response($result ?? ['error' => 0, 'message' => []], ':ADMIN/resource_resource/');
    }

    public function moveAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['id', 'category_id', 'type']);
            if ($result['error'] === 0) {
                try {
                    if ($data['type'] === 'r') {
                        $model = new Model;
                        $model->setData([
                            'id' => $data['id'],
                            'category_id' => $data['category_id'] ?: null
                        ])->save();
                    } else {
                        $model = new Category;
                        $model->setData([
                            'id' => $data['id'],
                            'parent_id' => $data['category_id'] ?: null
                        ])->save();
                    }
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $result['message'][] = ['message' => $this->translate('An error detected while saving.'), 'level' => 'danger'];
                    $result['error'] = 1;
                }
            }
        }
        return $this->response($result ?? ['error' => 0, 'message' => []], ':ADMIN/resource_resource/');
    }

    public function renameAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['id', 'name', 'type']);
            if ($result['error'] === 0) {
                try {
                    if ($data['type'] === 'r') {
                        $model = new Model;
                        $model->setData([
                            'id' => $data['id'],
                            'uploaded_name' => $data['name']
                        ])->save();
                    } else {
                        $model = new Category;
                        if ($data['id']) {
                            $model->load($data['id']);
                        } else {
                            $user = (new Segment('admin'))->get('user');
                            $model->setData([
                                'store_id' => $user->offsetGet('store_id') ?: (empty($data['store_id']) ? null : $data['store_id']),
                                'parent_id' => ((int) $data['pid']) ?: null
                            ]);
                        }
                        $model->setData('name', [Bootstrap::getLanguage()->getId() => $data['name']])
                                ->save();
                        $result['data'] = ['id' => $model->getId()];
                    }
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $result['message'][] = ['message' => $this->translate('An error detected while saving.'), 'level' => 'danger'];
                    $result['error'] = 1;
                }
            }
        }
        return $this->response($result ?? ['error' => 0, 'message' => []], ':ADMIN/resource_resource/');
    }

}
