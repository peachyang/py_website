<?php

namespace Seahinet\Admin\ViewModel;

use Seahinet\Lib\Session\Segment;

class Config extends Edit
{

    protected $key = null;
    protected $elements = null;
    protected $config = null;
    protected $tab = null;
    protected $store = false;

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
            $column = $this->getColumn($item, $key, $this->getKey());
            if ($column) {
                $columns[$key] = $column;
            }
        }
        return $columns;
    }

    protected function getTab()
    {
        if (is_null($this->tab)) {
            $this->tab = $this->getChild('tabs');
        }
        return $this->tab;
    }

    protected function getColumn($item, $key, $prefix)
    {
        if (isset($item['scope']) && !in_array($this->getStore() ? 'store' : $this->getQuery('scope', 'merchant'), (array) $item['scope'])) {
            return null;
        }
        if (isset($item['children'])) {
            $result = [];
            foreach ($item['children'] as $ckey => $child) {
                $column = $this->getColumn($child, $ckey, $prefix . '/' . $key);
                if ($column) {
                    $result[$ckey] = $column;
                }
            }
            if (!empty($result)) {
                $this->getTab()->addTab($key, $item['label']);
            }
            return $result;
        }
        if (isset($item['source']) && is_subclass_of($item['source'], '\\Seahinet\\Lib\\Source\\SourceInterface')) {
            $item['options'] = (new $item['source'])->getSourceArray($item);
        }
        $item['value'] = $this->getConfig()[$prefix . '/' . $key] ? : (isset($item['default']) ? (string) $item['default'] : '');
        return $item;
    }

    public function getStore()
    {
        if ($this->store === false) {
            $segment = new Segment('admin');
            $this->store = $segment->get('user')->getStore();
        }
        return $this->store;
    }

}
