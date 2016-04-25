<?php

namespace Seahinet\Admin\Controller;

use Seahinet\Lib\Controller\AuthActionController;

class LanguageController extends AuthActionController
{

    public function editAction()
    {
        return $this->getLayout('admin_language_edit');
    }

}
