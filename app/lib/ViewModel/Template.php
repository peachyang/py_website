<?php

namespace Seahinet\Lib\ViewModel;

/**
 * Default view model
 */
class Template extends AbstractViewModel
{
    protected function getTemplateDate() {
        $config = $this->getConfig();
        $handler = Root::instance()->getHandler();
        if($config && isset($config[$handler])) {
            return $config[$handler];
        }
