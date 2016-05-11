<?php

namespace Seahinet\Resources\Controller;

use Seahinet\Lib\Controller\ActionController;

class ResizeController extends ActionController
{

    use \Seahinet\Resources\Traits\Resize;

    public function indexAction()
    {
        $file = BP . 'pub/resource/' . $this->getOption('file');
        if (!file_exists($file)) {
            return $this->notFoundAction();
        }
        $image = $this->resize($file, $this->getOption('width'), $this->getOption('height'));
        $image->save(BP . 'pub/resource/resized/' . $this->getOption('width') . 'x' . $this->getOption('height') . '/' . $this->getOption('file'));
        $image->show();
        exit;
    }

}
