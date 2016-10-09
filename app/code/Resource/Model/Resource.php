<?php

namespace Seahinet\Resource\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Resource\Model\Collection\Resource as Collection;

class Resource extends AbstractModel
{

    public static $errorMessage = [
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk',
        8 => 'A PHP extension stopped the file upload',
        'post_max_size' => 'The uploaded file exceeds the post_max_size directive in php.ini',
        'max_file_size' => 'File is too big',
        'min_file_size' => 'File is too small',
        'accept_file_types' => 'Filetype not allowed',
        'max_number_of_files' => 'Maximum number of files exceeded',
        'max_width' => 'Image exceeds maximum width',
        'min_width' => 'Image requires a minimum width',
        'max_height' => 'Image exceeds maximum height',
        'min_height' => 'Image requires a minimum height',
        'abort' => 'File upload aborted',
        'image_resize' => 'Failed to resize image'
    ];
    public static $options = [
        'path' => 'pub/resource/',
        'dir_mode' => 0755,
        'max_file_size' => null,
        'min_file_size' => 1,
        'max_number_of_files' => null,
        'image_file_types' => '/\.(gif|jpe?g|png)$/i',
        'max_width' => null,
        'max_height' => null,
        'min_width' => 1,
        'min_height' => 1
    ];

    protected function construct()
    {
        $this->init('resource', 'id', ['id', 'store_id', 'real_name', 'uploaded_name', 'md5', 'file_type', 'category_id', 'size', 'sort_order']);
    }

    /**
     * 
     * @param \Seahinet\Lib\Http\UploadedFile $file
     * @return Resource
     * @throws Exception
     */
    public function moveFile($file)
    {
        $newName = $file->getClientFilename();
        $type = substr($file->getClientMediaType(), 0, strpos($file->getClientMediaType(), '/') + 1);
        $path = BP . static::$options['path'];
        if (!is_dir($path . $type)) {
            mkdir($path . $type, static::$options['dir_mode'], true);
        }
        $md5 = md5($file->getStream()->getContents());
        $collection = new Collection;
        $collection->where(['md5' => $md5])->limit(1);
        if (count($collection)) {
            $newName = $collection[0]['real_name'];
        } else {
            while (file_exists($path . $type . $newName)) {
                $newName = preg_replace('/(\.[^\.]+$)/', random_int(0, 9) . '$1', $newName);
                if (strlen($newName) >= 120) {
                    throw new Exception('The file is existed.');
                }
            }
            $file->moveTo($path . $type . $newName);
        }
        $this->setData(['md5' => $md5, 'real_name' => $newName, 'size' => (int) $file->getSize()]);
        return $this;
    }

    protected function beforeRemove()
    {
        if ($this->getId()) {
            if (!$this->isLoaded) {
                $this->load($this->getId());
                $type = $this->storage['file_type'];
                $collection = new Collection;
                $collection->where(['md5' => $this->storage['md5']])
                ->where->notEqualTo('id', $this->getId());
                if (count($collection) === 0) {
                    unlink(static::$options['path'] . substr($type, 0, strpos($type, '/') + 1) . $this->storage['real_name']);
                }
            }
        }
        parent::beforeRemove();
    }

}
