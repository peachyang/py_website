<?php

namespace Seahinet\Lib\ViewModel;

use CSSmin;
use JShrink\Minifier;
use Seahinet\Lib\Stdlib\Singleton;

/**
 * Head view model
 */
final class Head extends AbstractViewModel implements Singleton
{

    protected static $instance = null;
    protected $title = '';
    protected $script = ['condition' => [], 'normal' => []];
    protected $link = ['condition' => [], 'normal' => []];
    protected $meta = [];

    private function __construct()
    {
        $this->setTemplate('page/head');
    }

    public static function instance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    public function getTitle()
    {
        return $this->translate($this->title ? $this->title : $this->translate('Default Title'));
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getMeta()
    {
        $result = '';
        foreach ($this->meta as $name => $content) {
            $result .= '<meta ' . (in_array($name, ['expires', 'refresh', 'set-cookie']) ? 'http-equiv="' : 'name="') . $name . '" content="' . $content . '" />';
        }
        return $result;
    }

    public function addMeta($name, $content)
    {
        $this->meta[$name] = $content;
        return $this;
    }

    public function setDescription($content)
    {
        return $this->addMeta('description', $content);
    }

    public function setKeywords($content)
    {
        return $this->addMeta('keywords', $content);
    }

    public function getScript()
    {
        $result = '';
        if (count($this->script['normal'])) {
            $result = $this->renderScript($this->script['normal']);
        }
        foreach ($this->script['condition'] as $condition => $scripts) {
            $result .= '<!--[if ' . $condition . (strpos($condition, '!ie') === false ? ']>' : ']><!-->');
            $result .= $this->renderScript($scripts);
            $result .= strpos($condition, '!ie') === false ? '<![endif]-->' : '<!--<![endif]-->';
        }
        return $result;
    }

    public function addScript($script, $condition = null)
    {
        if (is_null($condition)) {
            $this->script['normal'][] = $script;
        } else {
            if (!isset($this->script['condition'][$condition])) {
                $this->script['condition'][$condition] = [];
            }
            $this->script['condition'][$condition][] = $script;
        }
        return $this;
    }

    public function getLink()
    {
        $result = $this->renderLink($this->link['normal']);
        foreach ($this->link['condition'] as $condition => $links) {
            $result .= '<!--[if ' . $condition . (strpos($condition, '!ie') === false ? ']>' : ']><!-->');
            $result .= $this->renderLink($links);
            $result .= strpos($condition, '!ie') === false ? '<![endif]-->' : '<!--<![endif]-->';
        }
        return $result;
    }

    public function addLink($link, $type = 'stylesheet', $condition = null)
    {
        if (is_null($condition)) {
            $this->link['normal'][$link] = $type;
        } else {
            if (!isset($this->link['condition'][$condition])) {
                $this->link['condition'][$condition] = [];
            }
            $this->link['condition'][$condition][$link] = $type;
        }
        return $this;
    }

    protected function renderLink($links)
    {
        $result = '';
        $config = $this->getContainer()->get('config');
        $combine = $config['theme/global/combine_css'];
        $files = [];
        $prefix = 'pub/theme/' . $config[$this->isAdminPage() ? 'theme/backend/static' : 'theme/frontend/static'] . '/';
        foreach ($links as $link => $type) {
            if ($combine && $type === 'stylesheet' && strpos($link, '://') === false) {
                $files[] = $prefix . '/' . $link;
            } else {
                if (strpos($link, '://') === false) {
                    $link = $this->getPubUrl($link);
                }
                $result .= '<link href="' . $link . '" rel="' . $type . '" />';
            }
        }
        if ($combine) {
            $link = $this->getCombinedFile($files, true);
            $result .= '<link href="' . $link . '.css" rel="stylesheet" />';
        }
        return $result;
    }

    protected function renderScript($scripts)
    {
        $result = '';
        $config = $this->getContainer()->get('config');
        $combine = $config['theme/global/combine_js'];
        $files = [];
        $prefix = 'pub/theme/' . $config[$this->isAdminPage() ? 'theme/backend/static' : 'theme/frontend/static'] . '/';
        foreach ($scripts as $script) {
            if (is_string($script)) {
                $script = ['src' => $script];
            }
            if ($combine && strpos($script['src'], '://') === false) {
                $files[] = $prefix . $script['src'];
            } else {
                if (strpos($script['src'], '://') === false) {
                    $script['src'] = $this->getPubUrl($script['src']);
                }
                $result .= '<script type="text/javascript" ';
                if (is_string($script)) {
                    $script = ['src' => $script];
                }
                foreach ($script as $key => $value) {
                    $result .= $key . '="' . $value . '" ';
                }
                $result .= '></script>';
            }
        }
        if ($combine) {
            $link = $this->getCombinedFile($files, false);
            $result .= '<script type="text/javascript" src="' . $link . '.js"></script>';
        }
        return $result;
    }

    protected function getCombinedFile(array $files, $isCss)
    {
        $content = '';
        foreach ($files as $file) {
            $temp = file_get_contents(BP . $file);
            if ($isCss && substr($file, -4) !== '.css') {
                try {
                    $temp = $this->getContainer()->get('csspp')->compile($temp);
                } catch (Exception $e) {
                    $this->getContainer()->get('log')->logException($e);
                    $temp = '';
                }
            }
            $content .= $temp;
        }
        $name = md5(implode('', $files));
        if ($isCss) {
            $path = 'pub/theme/' . $this->getContainer()->get('config')[$this->isAdminPage() ? 'theme/backend/static' : 'theme/frontend/static'] . '/cache/css/';

            if (!file_exists(BP . $path . $name . '.css')) {
                if (!is_dir(BP . $path)) {
                    mkdir($path, 0777, true);
                }
                $adapter = new CSSmin;
                file_put_contents(BP . $path . $name . '.css', $adapter->run($content));
            }
        } else {
            $path = 'pub/theme/' . $this->getContainer()->get('config')[$this->isAdminPage() ? 'theme/backend/static' : 'theme/frontend/static'] . '/cache/js/';

            if (!file_exists(BP . $path . $name . '.js')) {
                if (!is_dir(BP . $path)) {
                    mkdir($path, 0777, true);
                }
                file_put_contents(BP . $path . $name . '.js', Minifier::minify($content));
            }
        }
        return $this->getPubUrl('cache/' . ($isCss ? 'css/' : 'js/') . $name);
    }

}
