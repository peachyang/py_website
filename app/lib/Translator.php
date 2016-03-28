<?php

namespace Seahinet\Lib;

use Locale;
use Seahinet\Lib\Stdlib\Singleton;
use Seahinet\Lib\Translator\Category;
use SplFileObject;
use Symfony\Component\Finder\Finder;

class Translator implements Singleton
{

    use Traits\Container;

    const DEFAULT_DOMAIN = 'default';
    const CACHE_KEY = 'SEAHINET_TRANSLATOR_PAIRS_';

    private static $instance = null;
    protected $storage = [];
    protected $locale = null;
    protected static $defaultLocale = null;

    private function __construct($locale = null)
    {
        $this->setLocale($locale ? : $this->getContainer()->get('config')['locale']);
    }

    public static function instance($locale = null)
    {
        if (is_null(static::$instance)) {
            static::$instance = new static($locale);
            static::$defaultLocale = Locale::getDefault();
        }
        return static::$instance;
    }

    public static function setDefaultLocale($locale)
    {
        static::$defaultLocale = $locale;
    }

    public static function getDefaultLocale()
    {
        return static::$defaultLocale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    public function getLocale()
    {
        return $this->locale? : static::getDefaultLocale();
    }

    protected function loadMessages($locale)
    {
        if (!isset($this->storage[$locale])) {
            $cache = $this->getContainer()->get('cache');
            if ($cache) {
                $result = $cache->fetch(static::CACHE_KEY . $locale);
                if ($result) {
                    $this->storage[$locale] = $result;
                    return $this->storage[$locale];
                }
            }
            $this->storage[$locale] = ['default' => new Category()];
            $finder = new Finder();
            $finder->files()->in(BP . 'app/i18n/' . $locale)->name('*.csv');
            foreach ($finder as $file) {
                if (is_readable($file->getRealPath())) {
                    $domain = str_replace('.csv', '', $file->getFilename());
                    $this->storage[$locale][$domain] = $this->readFile($file->getRealPath());
                    $this->storage[$locale]['default']->merge($this->storage[$locale][$domain]);
                }
            }
            if ($cache) {
                $cache->save(static::CACHE_KEY . $locale, $this->storage[$locale]);
            }
        }
        return $this->storage[$locale];
    }

    protected function readFile($path)
    {
        $messages = new Category();

        $file = new SplFileObject($path, 'rb');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

        foreach ($file as $data) {
            if ('#' !== substr($data[0], 0, 1) && isset($data[1]) && 2 === count($data)) {
                $messages->offsetSet($data[0], $data[1]);
            }
        }

        return $messages;
    }

    public function __invoke($message, array $parameters = [], $domain = null, $locale = null)
    {
        return $this->translate($message, $parameters, $domain, $locale);
    }

    public function translate($message, array $parameters = [], $domain = null, $locale = null)
    {
        if (is_null($locale)) {
            $locale = $this->getLocale();
        }
        if (!$message) {
            return '';
        }
        $messages = $this->loadMessages($locale);
        if (empty($messages)) {
            return '';
        } else if (!is_null($domain) && $messages[$domain]->offsetExists($message)) {
            return vsprintf($messages[$domain]->offsetGet($message), $parameters);
        } else if (isset($messages[static::DEFAULT_DOMAIN]->offsetExists($message))) {
            return vsprintf($messages[static::DEFAULT_DOMAIN]->offsetGet($message), $parameters);
        } else {
            return vsprintf($message, $parameters);
        }
    }

}
