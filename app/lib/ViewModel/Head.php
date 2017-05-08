<?php

namespace Seahinet\Lib\ViewModel;

use CssMin;
use Error;
use Exception;
use JShrink\Minifier;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Stdlib\Singleton;

/**
 * Head view model
 */
final class Head extends Template implements Singleton
{

    protected static $instance = null;
    protected $title = '';
    protected $description = '';
    protected $keywords = '';
    protected $script = ['condition' => [], 'normal' => []];
    protected $link = ['condition' => [], 'normal' => []];
    protected $meta = [];
    protected $base = null;

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

    public function getBase()
    {
        return $this->base;
    }

    public function setBase($base)
    {
        $this->base = $base;
        return $this;
    }

    /**
     * Get translated title for title element
     * 
     * @return string
     */
    public function getTitle()
    {
        return $this->translate($this->title ? $this->title : $this->getConfig()['theme/global/default_title']);
    }

    /**
     * Set title for title element
     * 
     * @param string $title
     * @return Head
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get meta elements
     * 
     * @return string
     */
    public function getMeta()
    {
        $result = '';
        foreach ($this->meta as $name => $content) {
            $result .= '<meta ' . (in_array($name, ['expires', 'refresh', 'set-cookie']) ? 'http-equiv="' : 'name="') . $name . '" content="' . $content . '" />';
        }
        return $result;
    }

    /**
     * Add meta infomation
     * 
     * @param string $name
     * @param string $content
     * @return Head
     */
    public function addMeta($name, $content)
    {
        $this->meta[$name] = $content;
        return $this;
    }

    /**
     * Set meta description
     * 
     * @param string $content
     * @return Head
     */
    public function setDescription($content)
    {
        $this->description = $content;
        return $this;
    }

    /**
     * Set meta keywords
     * 
     * @param string $content
     * @return Head
     */
    public function setKeywords($content)
    {
        $this->keywords = $content;
        return $this;
    }

    /**
     * Set meta description
     * 
     * @param string $content
     * @return Head
     */
    public function getDescription()
    {
        return $this->description ? $this->description : $this->translate($this->getConfig()['theme/global/default_description']);
    }

    /**
     * Set meta keywords
     * 
     * @param string $content
     * @return Head
     */
    public function getKeywords()
    {
        return $this->keywords ? $this->keywords : $this->translate($this->getConfig()['theme/global/default_keywords']);
    }

    /**
     * Get script elements
     * 
     * @return string
     */
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

    /**
     * Add script infomation
     * 
     * @param string|array $script
     * @param string $condition
     * @return Head
     */
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

    /**
     * Get link elements
     * 
     * @return string
     */
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

    /**
     * Add link infomation
     * 
     * @param string $link
     * @param string $type
     * @param string $condition
     * @return Head
     */
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

    /**
     * Render links' array to HTML
     * 
     * @param array $links
     * @return string
     */
    protected function renderLink($links)
    {
        $result = '';
        $config = $this->getContainer()->get('config');
        $combine = $config['theme/global/combine_css'];
        $files = [];
        $prefix = 'pub/theme/' . $config[$this->isAdminPage() ? 'theme/backend/static' : 'theme/frontend/static'] . '/';
        foreach ($links as $link => $type) {
            if ($combine && $type === 'stylesheet' && strpos($link, '//') === false) {
                $files[] = $prefix . '/' . $link;
            } else if ($type !== 'stylesheet' || substr($link, -4) === '.css') {
                if (strpos($link, '//') === false) {
                    $link = $this->getPubUrl($link);
                }
                $result .= '<link href="' . $link . '" rel="' . $type . '" />';
            } else if ($type === 'stylesheet' && substr($link, -4) !== '.css' && strpos($link, '//') === false) {
                $filename = $prefix . substr($link, 0, strrpos($link, '.')) . '.css';
                if (!file_exists(BP . $filename) && file_exists(BP . $prefix . $link)) {
                    try {
                        $temp = $this->getContainer()->get('csspp')->compile(file_get_contents(BP . $prefix . $link));
                    } catch (Error $e) {
                        $this->getContainer()->get('log')->logError($e);
                        $temp = '';
                    } catch (Exception $e) {
                        $this->getContainer()->get('log')->logException($e);
                        $temp = '';
                    }
                    file_put_contents(BP . $filename, CssMin::minify($temp));
                }
                $result .= '<link href="' . $this->getBaseUrl($filename) . '" rel="stylesheet" />';
            }
        }
        if ($combine) {
            $link = $this->getCombinedFile($files, true);
            $result .= '<link href="' . $link . '.css" rel="stylesheet" />';
        }
        return $result;
    }

    /**
     * Render scripts' array to HTML
     * 
     * @param array $scripts
     * @return string
     */
    protected function renderScript($scripts)
    {
        $result = '';
        $config = $this->getContainer()->get('config');
        $combine = $config['theme/global/combine_js'];
        $files = [];
        $prefix = 'pub/theme/' . $config[$this->isAdminPage() ? 'theme/backend/static' : 'theme/frontend/static'] . '/';
        $sorted = [];
        $defered = [];
        foreach ($scripts as $script) {
            if (is_array($script) && isset($script['defer'])) {
                $defered[] = $script;
            } else {
                $sorted[] = $script;
            }
        }
        foreach (array_merge($sorted, $defered) as $script) {
            if (is_string($script)) {
                $script = ['src' => $script];
            }
            if ($combine && strpos($script['src'], '//') === false) {
                $files[] = $prefix . $script['src'];
            } else {
                if (strpos($script['src'], '//') === false) {
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

    /**
     * Combine js/css files
     * 
     * @param array $files
     * @param bool $isCss
     * @return string Combined url
     */
    protected function getCombinedFile(array $files, $isCss)
    {
        $content = '';
        sort($files);
        foreach ($files as $file) {
            $temp = file_get_contents(BP . $file);
            if ($isCss && substr($file, -4) !== '.css') {
                try {
                    $temp = $this->getContainer()->get('csspp')->compile($temp);
                } catch (Error $e) {
                    $this->getContainer()->get('log')->logError($e);
                    $temp = '';
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
                file_put_contents(BP . $path . $name . '.css', CssMin::minify($content));
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

    /**
     * Get locale code
     * 
     * @return string
     */
    public function getLocale()
    {
        return Bootstrap::getLanguage()->offsetGet('code');
    }

    public function getFormat()
    {
        $currency = $this->getContainer()->get('currency');
        return preg_replace('/\%(?:\d+\$)?s/', $currency->offsetGet('symbol'), $currency->offsetGet('format'));
    }

}
