<?php

namespace Seahinet\Resource\Model;

use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Lib\Session\Segment;
use Imagine\Image\Box;

/**
 * System backend user
 */
class Resource extends AbstractModel
{


    protected $error_messages;
    protected $options;
    
    protected function construct()
    {
        $this->init('resource', 'id', ['id', 'store_id', 'file_name', 'old_name', 'file_type', 'category_id']);
        $this->error_messages= array(
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
        );
        
        $this->options = array(
            'script_url' =>'',
            'upload_dir' => '/home/html/ecomv2admin/pub/Resource/',
            'upload_url' => 'http://ecomv2admin/lh.com/pub/Resource/',
            'input_stream' => 'php://input',
            'user_dirs' => false,
            'mkdir_mode' => 0755,
            'file_types'=>array('others'=>'others','images'=>'images','images'=>'video','images'=>'pdf'),
            // Defines which files (based on their names) are accepted for upload:
            'accept_file_types' => '/.+$/i',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => null,
            'min_file_size' => 1,
            // The maximum number of files for the upload directory:
            'max_number_of_files' => null,
            // Defines which files are handled as image files:
            'image_file_types' => '/\.(gif|jpe?g|png)$/i',
            // Image resolution restrictions:
            'max_width' => null,
            'max_height' => null,
            'min_width' => 1,
            'min_height' => 1,
        
            'image_versions' => array(
                '50x50' => array (
                    'max_width' => 50,
                    'max_height' => 50,
                    'quality' => 100
                ),
                '100x100' => array (
                    'max_width' => 100,
                    'max_height' => 100,
                    'quality' => 100
                ),
                '150x150' => array (
                    'max_width' => 150,
                    'max_height' =>150,
                    'quality' => 100
                ),
                '200x200' => array (
                    'max_width' => 200,
                    'max_height' => 200,
                    'quality' => 100
                ),
                '400x400' => array (
                    'max_width' => 400,
                    'max_height' => 400,
                    'quality' => 100
                ),
                '800x800' => array (
                    'max_width' => 800,
                    'max_height' => 800,
                    'quality' => 100
                )
            )
        );
        if(!is_dir($this->options['upload_dir'])){
            mkdir($this->options['upload_dir'], $this->options['mkdir_mode'], true);
        }
    }
     
     
     /**
      * @return upload target path
      */
     public function getTargetPath($fileType)
     {
     
         if(isset($this->options['file_types'][$fileType])&&$this->options['file_types'][$fileType]!=''){
             if (!is_dir($this->options['upload_dir'].$this->options['file_types'][$fileType])) {
                 mkdir($this->options['upload_dir'].$this->options['file_types'][$fileType],$this->options['mkdir_mode'],true);
             }
             if($fileType=="images"){
                 foreach ($this->options['image_versions'] as $k=>$v){
                     if(!is_dir($this->options['upload_dir'].$this->options['file_types'][$fileType].'/'.$k)){
                         mkdir($this->options['upload_dir'].$this->options['file_types'][$fileType].'/'.$k,$this->options['mkdir_mode'],true);
                     }
                 }
             }
         }else{
             $fileType='others';
             if (!is_dir($this->options['upload_dir'].$this->options['file_types'][$fileType])) {
                 mkdir($this->options['upload_dir'].$this->options['file_types'][$fileType],$this->options['mkdir_mode'],true);
             }
         }
         return $this->options['upload_dir'].$this->options['file_types'][$fileType].'/';
     }
     
     public function createImagesThumbnail($imagePath,$targetPath,$fileName){
         $imagine=$this->getContainer()->get('imagine');
         foreach ($this->options['image_versions'] as $k => $v){ 
             $imagine->open($imagePath)->thumbnail(new Box($v['max_width'], $v['max_height']))->save($targetPath.$k.'/'.$fileName);
         }
         return true;
     }

     
     
     
     
    protected function validate($uploaded_file, $file, $error, $index) {
        if ($error) {
            $file->error = $this->get_error_message($error);
            return false;
        }
        $content_length = $this->fix_integer_overflow(
            (int)$this->get_server_var('CONTENT_LENGTH')
            );
        $post_max_size = $this->get_config_bytes(ini_get('post_max_size'));
        if ($post_max_size && ($content_length > $post_max_size)) {
            $file->error = $this->get_error_message('post_max_size');
            return false;
        }
        if (!preg_match($this->options['accept_file_types'], $file->name)) {
            $file->error = $this->get_error_message('accept_file_types');
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
                $file->error = $this->get_error_message('max_file_size');
                return false;
            }
            if ($this->options['min_file_size'] &&
                $file_size < $this->options['min_file_size']) {
                    $file->error = $this->get_error_message('min_file_size');
                    return false;
                }
                if (is_int($this->options['max_number_of_files']) &&
                    ($this->count_file_objects() >= $this->options['max_number_of_files']) &&
                    // Ignore additional chunks of existing files:
                    !is_file($this->get_upload_path($file->name))) {
                        $file->error = $this->get_error_message('max_number_of_files');
                        return false;
                    }
                    $max_width = @$this->options['max_width'];
                    $max_height = @$this->options['max_height'];
                    $min_width = @$this->options['min_width'];
                    $min_height = @$this->options['min_height'];
                    if (($max_width || $max_height || $min_width || $min_height)
                        && preg_match($this->options['image_file_types'], $file->name)) {
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
                                $file->error = $this->get_error_message('max_width');
                                return false;
                            }
                            if ($max_height && $img_height > $max_height) {
                                $file->error = $this->get_error_message('max_height');
                                return false;
                            }
                            if ($min_width && $img_width < $min_width) {
                                $file->error = $this->get_error_message('min_width');
                                return false;
                            }
                            if ($min_height && $img_height < $min_height) {
                                $file->error = $this->get_error_message('min_height');
                                return false;
                            }
                        }
                        return true;
    }
    
    protected function get_error_message($error) {
        return isset($this->error_messages[$error]) ?
        $this->error_messages[$error] : $error;
    }
    
    protected function get_file_name($file_path, $name, $size, $type, $error,
        $index, $content_range) {
            $name = $this->trim_file_name($file_path, $name, $size, $type, $error,
                $index, $content_range);
            return $this->get_unique_filename(
                $file_path,
                $this->fix_file_extension($file_path, $name, $size, $type, $error,
                    $index, $content_range),
                $size,
                $type,
                $error,
                $index,
                $content_range
                );
    }
    
    protected function beforeSave()
    {
       
        parent::beforeSave();
    }

}
