<?php

namespace Seahinet\Lib\ViewModel;

use Error;
use Exception;
use JsonSerializable;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Session\Segment;

/**
 * Default view model
 */
class Template extends AbstractViewModel
{

    private static $isMobile = null;

    public function isMobile()
    {
        if (is_null(self::$isMobile)) {
            self::$isMobile = preg_match('/iPhone|iPod|BlackBerry|Palm|Googlebot-Mobile|Mobile|mobile|mobi|Windows Mobile|Safari Mobile|Android|Opera Mini/', $_SERVER['HTTP_USER_AGENT']) ? 'mobile_' : '';
        }
        return self::$isMobile;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        try {
            if (!$this->getTemplate()) {
                return $this instanceof JsonSerializable ? $this->jsonSerialize() : '';
            }
            if ($this->getCacheKey()) {
                $lang = Bootstrap::getLanguage()['code'];
                $cache = $this->getContainer()->get('cache');
                $rendered = $cache->fetch($lang . $this->getCacheKey(), 'VIEWMODEL_RENDERED_');
                if ($rendered) {
                    return $rendered;
                }
            }
            $template = BP . 'app/tpl/' . $this->getConfig()[$this->isAdminPage() ?
                            'theme/backend/' . ($this->isMobile() ? 'mobile_' : '') . 'template' :
                            'theme/frontend/' . ($this->isMobile() ? 'mobile_' : '') . 'template'] .
                    DS . $this->getTemplate();
            if ($this->getContainer()->has('renderer')) {
                $rendered = $this->getContainer()->get('renderer')->render($template, $this);
            } else if (file_exists($template . '.phtml')) {
                $rendered = $this->getRendered($template . '.phtml');
            } else if (file_exists($template = BP . 'app/tpl/default/' . $this->getTemplate() . '.phtml')) {
                $rendered = $this->getRendered($template);
            } else {
                $rendered = '';
            }
            if ($this->getCacheKey()) {
                $cache->save($lang . $this->getCacheKey(), $rendered, 'VIEWMODEL_RENDERED_');
            }
            return $rendered;
        } catch (Exception $e) {
            $this->getContainer()->get('log')->logException($e);
            return '';
        }
    }

    /**
     * Render template by default
     * 
     * @param string $template
     * @return string
     */
    protected function getRendered($template)
    {
        try {
            ob_start();
            include $template;
            return ob_get_clean();
        } catch (Error $e) {
            $this->getContainer()->get('log')->logError($e);
            ob_end_clean();
            return '';
        }
    }

    public function getSegment($name)
    {
        return new Segment($name);
    }

}
