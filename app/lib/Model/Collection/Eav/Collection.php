<?php

namespace Seahinet\Lib\Model\Collection\Eav;

use Seahinet\Lib\Model\AbstractCollection;
use Zend\Db\TableGateway\TableGateway;

abstract class Collection extends AbstractCollection
{

    protected $languageId = 0;

    public function __construct($languageId)
    {
        $this->languageId = $languageId;
        parent::__construct();
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
                if ($result = $this->loadFromDb()) {
                    $this->afterLoad($result);
                    if ($useCache) {
                        $this->addCacheList($cacheKey, $result, $this->getCacheKey());
                    }
                }
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
        return $this->getContainer()->get('indexer')->select($this->entityType, $this->languageId);
    }

    protected function loadFromDb()
    {
        
    }

}
