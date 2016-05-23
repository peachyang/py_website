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
        return (string) implode('', (array) $this->getVariables());
    }

}
