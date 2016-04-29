<?php

namespace Seahinet\Admin\ViewModel;

class Config extends Edit
{

    protected $key = null;
    protected $elements = null;
    protected $config = null;
    protected $tab = null;

    public function __construct()
    {
        $this->setTemplate('admin/config');
    }

    public function getSaveUrl()
    {
        return $this->getAdminUrl('config/save/');
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getElements()
    {
        if (is_null($this->elements)) {
            $this->elements = $this->getContainer()->get('config')['system'][$this->getKey()]['children'];
        }
        return $this->elements;
    }

    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    public function setElements($elements)
    {
        $this->elements = $elements;
        return $this;
    }

    protected function getRendered()
    {
        if (!$this->getKey()) {
            return '';
        }
        return parent::getRendered();
    }

    protected function getConfig()
    {
        if (is_null($this->config)) {
            $this->config = $this->getContainer()->get('config');
        }
        return $this->config;
    }

    protected function prepareElements($columns = [])
    {
        foreach ((array) $this->getElements() as $key => $item) {
            $columns[$key] = $this->getColumn($item, $key, $this->getKey());
        }
        return $columns;
    }

    protected function getTab()
    {
        if (is_null($this->tab)) {
            $this->tab = $this->getChild('tab');
        }
        return $this->tab;
    }

    protected function getColumn($item, $key, $prefix)
    {
        if (isset($item['children'])) {
            $result = [];
            $this->getTab()->addTab($key, $item['label']);
            foreach ($item['children'] as $ckey => $child) {
                $result[$ckey] = $this->getColumn($child, $ckey, $prefix . '/' . $key);
            }
            return $result;
        }
        $item['value'] = $this->getConfig()[$prefix . '/' . $key] ? : (isset($item['default']) ? (string)$item['default'] : '');
        return $item;
    }

}
