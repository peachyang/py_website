<?php

namespace Seahinet\Admin\Controller\Resource;

use Exception;
use Seahinet\Lib\Controller\AuthActionController;
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
            $store = (new Segment('admin'))->get('user')->getStore();
            $result = $this->validateForm($data);
            if ($result['error'] === 0) {
                try {
                    $path = BP . Model::$options['path'];
                    $mode = Model::$options['dir_mode'];
                    foreach ($files as $file) {
                        $name = $file->getClientFilename();
                        $info = $this->moveFile($file, $path, $mode);
                        $model = new Model();
                        $model->setData($info + [
                            'store_id' => $store ? $store->getId() : (isset($data['store_id']) && $data['store_id'] ? $data['store_id'] : null),
                            'uploaded_name' => $name,
                            'file_type' => $file->getClientMediaType(),
                            'category_id' => isset($data['category_id']) && $data['category_id'] ? $data['category_id'] : null
                        ])->save();
                        $result['message'][] = ['message' => $this->translate('%s has been uploaded successfully.', [$name], 'resource'), 'level' => 'success'];
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
        return $this->response($result, ':ADMIN/resource_resource/');
    }

    public function popupAction()
    {
        return $this->getLayout('admin_popup_images_list');
    }

    /**
     * 
     * @param \Seahinet\Lib\Http\UploadedFile $file
     * @param string $path
     * @param int $mode
     * @return array
     * @throws Exception
     */
    protected function moveFile($file, $path, $mode)
    {
        $newName = $file->getClientFilename();
        $type = substr($file->getClientMediaType(), 0, strpos($file->getClientMediaType(), '/') + 1);
        if (!is_dir($path . $type)) {
            mkdir($path . $type, $mode, true);
        }
        $md5 = md5($file->getStream()->getContents());
        $collection = new Collection;
        $collection->where(['md5' => $md5])->limit(1);
        if (count($collection)) {
            return ['md5' => $md5, 'real_name' => $collection[0]['real_name']];
        }
        while (file_exists($path . $type . $newName)) {
            $newName = preg_replace('/(\.[^\.]+$)/', random_int(0, 9) . '$1', $newName);
            if (strlen($newName) >= 120) {
                throw new Exception('The file is existed.');
            }
        }
        $file->moveTo($path . $type . $newName);
        return ['md5' => $md5, 'real_name' => $newName];
    }

}
