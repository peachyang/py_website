<?php

namespace Seahinet\Lib\ViewModel;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\Collection\Language as LanguageCollection;

class Language extends AbstractViewModel
{

    public function __construct()
    {
        $this->setTemplate('page/language');
    }

    public function getLanguage()
    {
        $language = new LanguageCollection;
        $store = Bootstrap::getStore();
        $language->where(['store_id' => Bootstrap::getStore()->getId(), 'status' => 1]);
        return $language;
    }

    public function getUrl($language)
    {
        return $this->getBaseUrl('language/switch/' . $language['code']);
    }

    public function getCurrentLanguage()
    {
        return Bootstrap::getLanguage()['name'];
    }

}
