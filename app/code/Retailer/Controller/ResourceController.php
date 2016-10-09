<?php

namespace Seahinet\Retailer\Controller;

use Exception;
use Seahinet\Lib\Session\Segment;
use Seahinet\Resource\Model\Resource as Model;
use Seahinet\Resource\Model\Collection\Resource as Collection;

class ResourceController extends AuthActionController
{

    public function indexAction()
    {
        $root = $this->getLayout('admin_resource_list');
        return $root;
    }

    public function uploadAction()
    {
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $files = $this->getRequest()->getUploadedFile()['files'];
            $retailer = (new Segment('customer'))->get('customer')->getRetailer();
            $result = $this->validateForm($data);
            if ($result['error'] === 0) {
                try {
                    foreach ($files as $file) {
                        $name = $file->getClientFilename();
                        $model = new Model();
                        $model->moveFile($file)
                                ->setData([
                                    'store_id' => $retailer ? $retailer->offsetGet('store_id') : (empty($data['store_id']) ? null : $data['store_id']),
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
        $result = ['error' => 0, 'message' => []];
        if ($this->getRequest()->isDelete()) {
            $data = $this->getRequest()->getPost();
            $result = $this->validateForm($data, ['id']);
            if ($result['error'] === 0) {
                try {
                    $path = BP . Model::$options['path'];
                    $count = 0;
                    foreach ((array) $data['id'] as $id) {
                        $model = new Model;
                        $model->load($id);
                        if ($model->getId()) {
                            $type = $model['file_type'];
                            $collection = new Collection;
                            $collection->where(['md5' => $model['md5']])
                                    ->where('id <> ' . $id);
                            if (count($collection) === 0) {
                                unlink($path . substr($type, 0, strpos($type, '/') + 1) . $model['real_name']);
                            }
                            $model->remove();
                            $count++;
                        }
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
        return $this->response($result, ':retailer/resource/');
    }

    public function popupAction()
    {
        return $this->getLayout('retailer_popup_images_list');
    }

}
