<?php

namespace Seahinet\Lib\Model\Collection\Eav;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\AbstractCollection;

abstract class Collection extends AbstractCollection
{

    const ENTITY_TYPE = '';

    protected $languageId = 0;

    public function __construct($languageId = 0)
    {
        if ($languageId) {
            $this->languageId = $languageId;
        } else {
            $this->languageId = Bootstrap::getLanguage()->getId();
        }
        $this->init();
    }

    protected function init($null = null)
    {
        $this->tableName = static::ENTITY_TYPE . '_' . $this->languageId . '_index';
        $this->getTableGateway($this->tableName);
        $this->cacheKey = static::ENTITY_TYPE;
        if (is_null($this->select)) {
            $this->select = $this->getTableGateway($this->tableName)->getSql()->select();
        }
    }

    public function load($useCache = true)
    {
        if (!$this->isLoaded) {
            try {
                $cacheKey = md5($this->select->getSqlString($this->getTableGateway($this->tableName)->getAdapter()->getPlatform()));
                if ($useCache && ($result = $this->loadFromCache($cacheKey))) {
                    $this->afterLoad($result);
                } else if ($result = $this->loadFromIndexer()) {
                    $this->afterLoad($result);
                    if ($useCache) {
                        $this->addCacheList($cacheKey, $result, $this->getCacheKey());
                    }
                }
            } catch (BadIndexerException $e) {
                $this->afterLoad([]);
            } catch (InvalidQueryException $e) {
                $this->getContainer()->get('log')->logException($e);
                throw $e;
            } catch (Exception $e) {
                $this->getContainer()->get('log')->logException($e);
                throw $e;
            }
        }
    }

    protected function loadFromCache($cacheKey)
    {
        return $this->fetchList($cacheKey, $this->getCacheKey());
    }

    protected function loadFromIndexer()
    {
        return $this->getContainer()->get('indexer')->select(static::ENTITY_TYPE, $this->languageId, $this->select);
    }

}
