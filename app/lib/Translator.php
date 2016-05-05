<?php

namespace Seahinet\Lib;

use Locale;
use Seahinet\Lib\Model\Collection\Translate;
use Seahinet\Lib\Stdlib\Singleton;
use Seahinet\Lib\Translator\Category;
use SplFileObject;
use Symfony\Component\Finder\Finder;

/**
 * Translate service
 */
class Translator implements Singleton
{

    use Traits\Container;

    const DEFAULT_DOMAIN = 'default';
    const CACHE_KEY = 'TRANSLATOR_PAIRS_';

    /**
     * @var Translator
     */
    protected static $instance = null;

    /**
     * @var array
     */
    protected $storage = [];

    /**
     * @var string
     */
    protected $locale = null;

    /**
     * @var string
     */
    protected static $defaultLocale = null;

    /**
     * @param string|Container $locale
     */
    private function __construct($locale = null)
    {
        if ($locale instanceof Container) {
            $this->setContainer($locale);
            $locale = null;
        }
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

    /**
     * @param string $locale
     */
    public static function setDefaultLocale($locale)
    {
        static::$defaultLocale = $locale;
    }

    /**
     * @return string
     */
    public static function getDefaultLocale()
    {
        return static::$defaultLocale;
    }

    /**
     * @param string $locale
     * @return Translator
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale? : static::getDefaultLocale();
    }

    /**
     * Load translate pairs from csv files
     * 
     * @param string $locale
     * @return array of Category
     */
    protected function loadMessages($locale)
    {
        if (!isset($this->storage[$locale])) {
            $cache = $this->getContainer()->get('cache');
            if ($cache) {
                $result = $cache->fetch($locale, static::CACHE_KEY);
                if ($result) {
                    $this->storage[$locale] = $result;
                    return $this->storage[$locale];
                }
            }
            $this->storage[$locale] = [];
            $collection = new Translate;
            $collection->where(['status' => 1, 'locale' => $locale]);
            $result = [];
            foreach ($collection as $item) {
                $result[$item['string']] = $item['translate'];
            }
            $this->storage[$locale][static::DEFAULT_DOMAIN] = new Category($result);
            $finder = new Finder();
            $finder->files()->in(BP . 'app/i18n/' . $locale)->name('*.csv');
            foreach ($finder as $file) {
                if (is_readable($file->getRealPath())) {
                    $domain = str_replace('.csv', '', $file->getFilename());
                    $this->storage[$locale][$domain] = $this->readFile($file->getRealPath());
                    $this->storage[$locale][static::DEFAULT_DOMAIN]->merge($this->storage[$locale][$domain]);
                }
            }
            if ($cache) {
                $cache->save($locale, $this->storage[$locale], static::CACHE_KEY);
            }
        }
        return $this->storage[$locale];
    }

    /**
     * @param string $path
     * @return Category
     */
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

    /**
     * Translate messages
     * 
     * @param string $message
     * @param array $parameters
     * @param string $domain
     * @param string $locale
     * @return string
     */
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
            return vsprintf($message, $parameters);
        } else if (!is_null($domain) && isset($messages[$domain]) && $messages[$domain]->offsetExists($message)) {
            return vsprintf($messages[$domain]->offsetGet($message), $parameters);
        } else if ($messages[static::DEFAULT_DOMAIN]->offsetExists($message)) {
            return vsprintf($messages[static::DEFAULT_DOMAIN]->offsetGet($message), $parameters);
        } else {
            return vsprintf($message, $parameters);
        }
    }

}
