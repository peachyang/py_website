<?php

namespace Seahinet\Lib\ViewModel;

use Seahinet\Lib\Stdlib\Singleton;

/**
 * Head view model
 */
final class Head extends AbstractViewModel implements Singleton
{

    private static $instance = null;
    private $title = '';
    private $script = ['condition' => [], 'normal' => []];
    private $link = ['condition' => [], 'normal' => []];
    private $meta = [];

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
        return $this->title? : $this->translate('Default Title');
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
        foreach ($links as $link => $type) {
            $result .= '<link href="' . $link . '" rel="' . $type . '" />';
        }
        return $result;
    }

    protected function renderScript($scripts)
    {
        return '<script type="text/javascript" src="' . implode('"></script><script type="text/javascript" src="', $scripts) . '"></script>';
    }

}
