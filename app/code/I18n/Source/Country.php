<?php

namespace Seahinet\I18n\Source;

use Collator;
use Seahinet\Lib\Source\SourceInterface;
use Seahinet\I18n\Model\Locate;
use Seahinet\Lib\Bootstrap;

class Country implements SourceInterface
{
    
    use \Seahinet\Lib\Traits\Container;

    public function getSourceArray()
    {
        $locate = new Locate;
        $result = [];
        $language = Bootstrap::getLanguage()['code'];
        $geoip = $this->getContainer()->get('geoip');
        $code = $geoip ? $geoip->get($_SERVER['REMOTE_ADDR'])['country']['iso_code'] : '';
        $default = false;
        foreach ($locate->getCountry() as $item) {
            if (isset($item['iso2_code']) && $item['iso2_code'] === $code) {
                $default = $item->getName($language);
            }
            $result[$item['iso2_code']] = $item->getName($language);
        }
        if (extension_loaded('intl')) {
            $collator = new Collator($language);
            $value_compare_func = function($str1, $str2) use ($collator, $default) {
                return $str1 === $default ? -1 : ($str2 === $default ? 1 : $collator->compare($str1, $str2));
            };
        } else {
            $value_compare_func = function($str1, $str2) use ($default) {
                return $str1 === $default ? -1 : ($str2 === $default ? 1 : strnatcmp($str1, $str2));
            };
        }
        uasort($result, $value_compare_func);
        return $result;
    }

}
