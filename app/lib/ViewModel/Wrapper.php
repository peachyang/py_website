<?php

namespace Seahinet\Lib\ViewModel;

use Error;

class Wrapper extends AbstractViewModel
{

    /**
     * Render variables and children view models strightly
     * 
     * @return string
     */
    public function render()
    {
        try {
            return implode('', $this->getChild());
        } catch (Error $e) {
            $this->getContainer()->get('log')->logError($e);
            return '';
        }
    }

}
