<?php

namespace Seahinet\Lib\Traits;

trait Translate
{

    /**
     * Translate messages
     * 
     * @param string $message
     * @param array $parameters
     * @param string $domain
     * @return string
     */
    protected function translate($message, $parameters = [], $domain = null)
    {
        try {
            return $this->getContainer()->get('translator')->translate($message, $parameters, $domain);
        } catch (\Exception $e) {
            return $message;
        }
    }

}
