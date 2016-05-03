<?php

namespace Seahinet\Lib\ViewModel;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\Collection\Language as LanguageCollection;

class Language extends AbstractViewModel
{

    protected $showEdit = false;
    protected $editUrl = null;

    public function __construct()
    {
        $this->setTemplate('page/language');
    }

    public function getLanguage()
    {
        $language = new LanguageCollection;
        $language->where(['merchant_id' => Bootstrap::getMerchant()->getId(), 'status' => 1]);
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

    public function showEdit($flag = null)
    {
        if (is_bool($flag)) {
            $this->showEdit = $flag;
        }
        return $this->showEdit;
    }

    public function getEditUrl()
    {
        return $this->isAdminPage() ? $this->getAdminUrl($this->editUrl) : $this->getBaseUrl($this->editUrl);
    }

    public function setEditUrl($editUrl)
    {
        $this->editUrl = $editUrl;
        return $this;
    }

}
