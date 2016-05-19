<?php

namespace Seahinet\Lib\ViewModel;

class Translate extends AbstractViewModel
{

    /**
     * Get translation pairs in array mode
     * 
     * @return array
     */
    protected function getTranslateData()
    {
        $config = $this->getConfig()['jstranslate'];
        $handler = Root::instance()->getHandler();
        if ($config && isset($config[$handler])) {
            return $config[$handler];
        }
        return [];
    }

    /**
     * Get translation pairs in json mode
     * 
     * @return string
     */
    protected function getTranslateJson()
    {
        $data = $this->getTranslateData();
        if ($data) {
            $result = [];
            foreach ($data as $sentence) {
                $result[$sentence] = $this->translate($sentence);
            }
            return json_encode($result);
        }
        return false;
    }

    /**
     * Return Javascript code to initialize js translator
     * 
     * @return string
     */
    public function render()
    {
        $data = $this->getTranslateJson();
        if ($data) {
            return 'translate(' . $data . ');';
        }
        return '';
    }

}
