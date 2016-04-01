<?php

namespace Seahinet\Lib\ViewModel;

class Wrapper extends AbstractViewModel
{

    public function render()
    {
        return implode('', $this->getVariables());
    }

}
