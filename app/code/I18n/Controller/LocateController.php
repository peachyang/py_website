<?php

namespace Seahinet\I18n\Controller;

use Collator;
use Seahinet\I18n\Model\Locate;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Controller\ActionController;

class LocateController extends ActionController
{

    public function indexAction()
    {
        $data = $this->getRequest()->getQuery();
        $locate = new Locate;
        $locale = Bootstrap::getLanguage()->offsetGet('code');
        $result = [];
        $geoip = $this->getContainer()->get('geoip');
        $code = $geoip ? $geoip->get($_SERVER['REMOTE_ADDR'])['country']['iso_code'] : '';
        if ($data) {
            foreach ($data as $part => $id) {
                $resultSet = $locate->load($part, $id);
                break;
            }
        } else {
            $resultSet = $locate->load('country');
            $config = $this->getContainer()->get('config');
            $enabled = $config['global/locale/enabled_country'];
            $disabled = $config['global/locale/disabled_country'];
        }
        foreach ($resultSet as $id => $item) {
            if (isset($item['iso2_code']) && $item['iso2_code'] === $code) {
                $default = [
                    'value' => $id,
                    'code' => $code,
                    'label' => $item->getName($locale)
                ];
            } else if ((empty($enabled) || in_array($item['iso2_code'], explode(',', $enabled))) && (empty($disabled) || !in_array($item['iso2_code'], explode(',', $disabled)))) {
                $result[] = [
                    'value' => $id,
                    'code' => $item['iso2_code'] ?? $item['code'],
                    'label' => $item->getName($locale)
                ];
            }
        }
        if (extension_loaded('intl')) {
            $collator = new Collator($locale);
            $value_compare_func = function($str1, $str2) use ($collator) {
                return $collator->compare($str1['label'], $str2['label']);
            };
        } else {
            $value_compare_func = function($str1, $str2) {
                return strnatcmp($str1['code'], $str2['code']);
            };
        }
        uasort($result, $value_compare_func);
        if (isset($default)) {
            array_unshift($result, $default);
        }
        return array_values($result);
    }

}
