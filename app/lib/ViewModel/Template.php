<?php

namespace Seahinet\Lib\ViewModel;

use Error;
use Exception;
use JsonSerializable;
use Seahinet\Lib\Bootstrap;

/**
 * Default view model
 */
class Template extends AbstractViewModel
{

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
                            'theme/backend/template' : 'theme/frontend/template'] .
                    DS . $this->getTemplate();
            if ($this->getContainer()->has('renderer')) {
                $rendered = $this->getContainer()->get('renderer')->render($template, $this);
            } else if (file_exists($template . '.phtml')) {
                $rendered = $this->getRendered($template . '.phtml');
            } else if (file_exists($template = BP . 'app/tpl/default/' . $this->getTemplate())) {
                $rendered = $this->getRendered($template . '.phtml');
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
            ob_clean();
            return '';
        }
    }

}
