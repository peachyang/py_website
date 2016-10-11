<?php

namespace Seahinet\Lib\Traits;

/**
 * Translate sentence
 */
trait Translate
{

    /**
     * Translate messages
     * 
     * @param string $message
     * @param array $parameters
     * @param string $domain
     * @param string $locale
     * @return string
     */
    protected function translate(string $message, $parameters = [], $domain = null, $locale = null)
    {
        try {
            return $this->getContainer()->get('translator')->translate($message, $parameters, $domain, $locale);
        } catch (\Exception $e) {
            return vsprintf($message, $parameters);
        }
    }

}
