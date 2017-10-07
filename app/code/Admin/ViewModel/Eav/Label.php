<?php

namespace Seahinet\Admin\ViewModel\Eav;

use Seahinet\Lib\Source\Language;
use Seahinet\Lib\ViewModel\Template;

class Label extends Template
{

    protected $languages = null;

    public function getLanguages()
    {
        if (is_null($this->languages)) {
            $this->languages = (new Language)->getSourceArray();
        }
        return $this->languages;
    }

}
