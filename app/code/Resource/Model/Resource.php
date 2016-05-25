<?php

namespace Seahinet\Resource\Model;

use Seahinet\Lib\Model\AbstractModel;
use Imagine\Image\Box;

/**
 * System backend user
 */
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
        $this->init('resource', 'id', ['id', 'store_id', 'real_name', 'uploaded_name', 'md5', 'file_type', 'category_id']);
    }

    /**
     * @return upload target path
     */
    public function getTargetPath($fileType)
    {
        if (isset($this->options['file_types'][$fileType]) && $this->options['file_types'][$fileType] != '') {
            if (!is_dir($this->options['upload_dir'] . $this->options['file_types'][$fileType])) {
                mkdir($this->options['upload_dir'] . $this->options['file_types'][$fileType], $this->options['mkdir_mode'], true);
            }
            if ($fileType == "images") {
                foreach ($this->options['image_versions'] as $k => $v) {
                    if (!is_dir($this->options['upload_dir'] . $this->options['file_types'][$fileType] . '/' . $k)) {
                        mkdir($this->options['upload_dir'] . $this->options['file_types'][$fileType] . '/' . $k, $this->options['mkdir_mode'], true);
                    }
                }
            }
        } else {
            $fileType = 'others';
            if (!is_dir($this->options['upload_dir'] . $this->options['file_types'][$fileType])) {
                mkdir($this->options['upload_dir'] . $this->options['file_types'][$fileType], $this->options['mkdir_mode'], true);
            }
        }
        return $this->options['upload_dir'] . $this->options['file_types'][$fileType] . '/';
    }

    public function createImagesThumbnail($imagePath, $targetPath, $fileName)
    {
        $imagine = $this->getContainer()->get('imagine');
        foreach ($this->options['image_versions'] as $k => $v) {
            $imagine->open($imagePath)
                    ->thumbnail(new Box($v['max_width'], $v['max_height']))
                    ->save($targetPath . $k . '/' . $fileName);
        }
        return true;
    }

    protected function validate($uploaded_file, $file, $error, $index)
    {
        if ($error) {
            $file->error = $this->getErrorMessage($error);
            return false;
        }
        $content_length = $this->fix_integer_overflow(
                (int) $this->get_server_var('CONTENT_LENGTH')
        );
        $post_max_size = $this->get_config_bytes(ini_get('post_max_size'));
        if ($post_max_size && ($content_length > $post_max_size)) {
            $file->error = $this->getErrorMessage('post_max_size');
            return false;
        }
        if (!preg_match($this->options['accept_file_types'], $file->name)) {
            $file->error = $this->getErrorMessage('accept_file_types');
            return false;
        }
        if ($uploaded_file && is_uploaded_file($uploaded_file)) {
            $file_size = $this->get_file_size($uploaded_file);
        } else {
            $file_size = $content_length;
        }
        if ($this->options['max_file_size'] && (
                $file_size > $this->options['max_file_size'] ||
                $file->size > $this->options['max_file_size'])
        ) {
            $file->error = $this->getErrorMessage('max_file_size');
            return false;
        }
        if ($this->options['min_file_size'] &&
                $file_size < $this->options['min_file_size']) {
            $file->error = $this->getErrorMessage('min_file_size');
            return false;
        }
        if (is_int($this->options['max_number_of_files']) &&
                ($this->count_file_objects() >= $this->options['max_number_of_files']) &&
                // Ignore additional chunks of existing files:
                !is_file($this->get_upload_path($file->name))) {
            $file->error = $this->getErrorMessage('max_number_of_files');
            return false;
        }
        $max_width = $this->options['max_width'];
        $max_height = $this->options['max_height'];
        $min_width = $this->options['min_width'];
        $min_height = $this->options['min_height'];
        if (($max_width || $max_height || $min_width || $min_height) && preg_match($this->options['image_file_types'], $file->name)) {
            list($img_width, $img_height) = $this->get_image_size($uploaded_file);

            // If we are auto rotating the image by default, do the checks on
            // the correct orientation
            if (
                    @$this->options['image_versions']['']['auto_orient'] &&
                    function_exists('exif_read_data') &&
                    ($exif = @exif_read_data($uploaded_file)) &&
                    (((int) @$exif['Orientation']) >= 5 )
            ) {
                $tmp = $img_width;
                $img_width = $img_height;
                $img_height = $tmp;
                unset($tmp);
            }
        }
        if (!empty($img_width)) {
            if ($max_width && $img_width > $max_width) {
                $file->error = $this->getErrorMessage('max_width');
                return false;
            }
            if ($max_height && $img_height > $max_height) {
                $file->error = $this->getErrorMessage('max_height');
                return false;
            }
            if ($min_width && $img_width < $min_width) {
                $file->error = $this->getErrorMessage('min_width');
                return false;
            }
            if ($min_height && $img_height < $min_height) {
                $file->error = $this->getErrorMessage('min_height');
                return false;
            }
        }
        return true;
    }

    protected function getErrorMessage($error)
    {
        return isset(self::$errorMessage[$error]) ?
                self::$errorMessage[$error] : $error;
    }

}
