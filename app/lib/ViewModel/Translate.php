<?php

namespace Seahinet\Lib\ViewModel;

class Translate extends AbstractViewModel
{

    protected function getTranslateData()
    {
        $config = $this->getConfig()['jstranslate'];
        $handler = Root::instance()->getHandler();
        if ($config && isset($config[$handler])) {
            return $config[$handler];
        }
        return [];
    }

    protected function getTranslateJson()
    {
        $data = $this->getTranslateData();
        $result = [];
        foreach ($data as $sentence) {
            $result[$sentence] = $this->translate($sentence);
        }
        return json_encode($result);
    }

    public function render()
    {
        $data = $this->getTranslateJson();
        if ($data) {
            return 'translate(' . $data . ');';
        }
        return '';
    }

}
