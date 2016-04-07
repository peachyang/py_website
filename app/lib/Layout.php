<?php

namespace Seahinet\Lib;

use Seahinet\Lib\Stdlib\ArrayObject;
use Seahinet\Lib\Stdlib\Singleton;

/**
 * Page layout manager
 */
class Layout extends ArrayObject implements Singleton
{

    use Traits\Container,
        Traits\ArrayMerge;

    protected static $layout = null;

    protected function __construct($config = [])
    {
        if ($config instanceof Container) {
            $this->setContainer($config);
            $config = [];
        }
        if (empty($config)) {
            $config = $this->getContainer()->get('config')['layout'];
        }
        $this->storage = $config;
    }

    public static function instance($config = [])
    {
        if (is_null(static::$layout)) {
            static::$layout = new static($config);
        }
        return static::$layout;
    }

    /**
     * Get page layout
     * 
     * @param string $handler
     * @param bool $render
     * @return array|ViewModel\Root
     */
    public function getLayout($handler = '', $render = false)
    {
        if (empty($this->storage[$handler])) {
            return [];
        }
        if ($render) {
            $cache = $this->getContainer()->get('cache');
            $result = $cache->fetch('LAYOUT_RENDERED_' . $handler);
            if ($result) {
                return $result;
            }
        }
        $layout = $this->storage[$handler];
        if (isset($this->storage[$handler]['update'])) {
            $layout = $this->arrayMerge($this->getLayout($this->storage[$handler]['update']), $layout);
        }
        if ($render) {
            $root = $this->renderLayout($layout['root'], 'root');
            $root->addBodyClass(trim(preg_replace('/[^a-z]/', '-', strtolower($handler)), '- '));
            $cache->save('LAYOUT_RENDERED_' . $handler, $root);
            return $root;
        }
        return $layout;
    }

    /**
     * 
     * @param array $layout
     * @param string $name
     * @param ViewModel\AbstractViewModel $parent
     * @return ViewModel\AbstractViewModel
     */
    public function renderLayout(array $layout, $name, $parent = null)
    {
        if (is_subclass_of($layout['type'], '\\Seahinet\\Lib\\Stdlib\\Singleton')) {
            $viewModel = $layout['type']::instance();
        } else {
            $viewModel = new $layout['type'];
        }
        foreach ($layout as $key => $value) {
            switch ($key) {
                case 'template':
                    $viewModel->setTemplate($value);
                    break;
                case 'action':
                    if (isset($value['method'])) {
                        $value = [$value];
                    }
                    foreach ($value as $action) {
                        call_user_func_array([$viewModel, $action['method']], isset($action['params']) ? (array) $action['params'] : []);
                    }
                    break;
                case 'children':
                    foreach ($value as $childName => $children) {
                        if (isset($layout['unset']) && in_array($childName, $layout['unset'])) {
                            continue;
                        }
                        $this->renderLayout($children, $childName, $viewModel);
                    }
                    break;
            }
        }
        if (!is_null($parent)) {
            $parent->addChild($name, $viewModel);
        }
        return $viewModel;
    }

}
