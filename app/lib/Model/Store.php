<?php

namespace Seahinet\Lib\Model;

use Seahinet\Lib\Model\Collection\Language;

class Store extends AbstractModel
{

    protected function _construct()
    {
        $this->init('core_store', 'id', ['id', 'merchant_id', 'code', 'status']);
    }

    public function getLanguage($code = null)
    {
        $lang = new Language;
        if (!is_null($code)) {
            $lang->where(['code' => $code, 'status' => 1]);
        } else {
            $lang->where(['is_default' => 1, 'status' => 1]);
        }
        $lang->load();
        return $lang[0];
    }

}
