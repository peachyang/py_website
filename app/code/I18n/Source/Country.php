<?php

namespace Seahinet\I18n\Source;

use Seahinet\Lib\Source\SourceInterface;
use Seahinet\I18n\Model\Locate;
use Seahinet\Lib\Bootstrap;

class Country implements SourceInterface
{

    public function getSourceArray()
    {
        $locate = new Locate;
        $result = [];
        $language = Bootstrap::getLanguage()['code'];
        foreach ($locate->getCountry() as $item) {
            $result[$item['iso2_code']] = $item->getName($language);
        }
        return $result;
    }

}
