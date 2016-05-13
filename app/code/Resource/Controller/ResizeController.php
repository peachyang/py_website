<?php

namespace Seahinet\Resource\Controller;

use Seahinet\Lib\Controller\ActionController;

class ResizeController extends ActionController
{

    use \Seahinet\Resource\Traits\Resize;

    public function indexAction()
    {
        $file = BP . 'pub/resource/images/' . $this->getOption('file');
        if (!file_exists($file)) {
            return $this->notFoundAction();
        }
        $resized = BP . 'pub/resource/images/resized/' . $this->getOption('width') . 'x' . $this->getOption('height') . '/' . $this->getOption('file');
        $path = dirname($resized);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $image = $this->resize($file, $this->getOption('width'), $this->getOption('height'));
        $image->save($resized);
        $image->show(substr($resized, strrpos($resized, '.') + 1));
        exit;
    }

}
