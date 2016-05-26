<?php

namespace Seahinet\Lib\ViewModel;

class Wrapper extends AbstractViewModel
{

    /**
     * Render variables and children view models strightly
     * 
     * @return string
     */
    public function render()
    {
        return implode('', $this->getVariables());
    }

}
