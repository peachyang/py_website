<?php

namespace Seahinet\I18n\Controller;

use Seahinet\I18n\Model\Locate;
use Seahinet\Lib\Controller\ActionController;

class LocateController extends ActionController
{

    public function indexAction()
    {
        $data = $this->getRequest()->getQuery();
        $locate = new Locate;
        $locale = \Seahinet\Lib\Bootstrap::getLanguage()->offsetGet('code');
        $result = [];
        if ($data) {
            foreach ($data as $part => $id) {
                $resultSet = $locate->load($part, $id);
                break;
            }
        } else {
            $resultSet = $locate->load('country');
        }
        foreach ($resultSet as $id => $item) {
            $result[] = [
                'value' => $id,
                'label' => $item->getName($locale)
            ];
        }
        uasort($result, function($a, $b) {
            $result = strcmp($a['label'], $b['label']);
            return $result > 0 ? 1 : ($result < 0 ? -1 : 0);
        });
        return array_values($result);
    }

}
