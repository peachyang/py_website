<?php

namespace Seahinet\Lib\Traits;

/**
 * Handle cache for data
 */
trait DataCache
{

    /**
     * @var \Seahinet\Lib\Cache
     */
    protected $cacheObject = null;

    /**
     * @var array 
     */
    protected $cachedData = [];

    /**
     * @var string
     */
    protected $dataCacheKey = null;

    /**
     * @return string
     */
    public function getDataCacheKey()
    {
        if (is_null($this->dataCacheKey) && is_callable([$this, 'getCacheKey'])) {
            $this->dataCacheKey = $this->getCacheKey();
        }
        return $this->dataCacheKey;
    }

    /**
     * @param string $dataCacheKey
     */
    public function setDataCacheKey($dataCacheKey)
    {
        $this->dataCacheKey = $dataCacheKey;
    }

    /**
     * Get cache object
     * 
     * @return \Seahinet\Lib\Cache
     */
    protected function getCacheObject()
    {
        if (is_null($this->cacheObject)) {
            $this->cacheObject = $this->getContainer()->get('cache');
        }
        return $this->cacheObject;
    }

    /**
     * Read cached data
     * 
     * @param string $cacheKey
     */
    protected function readCache($cacheKey)
    {
        if (!isset($this->cachedData[$cacheKey])) {
            $this->cachedData[$cacheKey] = $this->getCacheObject()->fetch($cacheKey, 'DATA_');
        }
        return $this->cachedData[$cacheKey];
    }

    /**
     * Write cached data
     * 
     * @param string $cacheKey
     */
    protected function writeCache($cacheKey)
    {
        if (isset($this->cachedData[$cacheKey])) {
            $this->getCacheObject()->save($cacheKey, $this->cachedData[$cacheKey], 'DATA_');
        }
    }

    /**
     * Delete cached data
     * 
     * @param string $cacheKey
     */
    protected function deleteCache($cacheKey)
    {
        $this->getCacheObject()->delete($cacheKey, 'DATA_');
    }

    /**
     * Fetch a row data by key
     * 
     * @param int|string $id
     * @param string $key
     * @param string $cacheKey
     * @return mixed
     */
    protected function fetchRow($id, $key = null, $cacheKey = null)
    {
        if (!is_null($cacheKey)) {
            $this->setDataCacheKey($cacheKey);
        }
        if (is_object($id) || is_array($id)) {
            $id = $id['id'];
        }
        $cacheKey = $this->getDataCacheKey() . (is_null($key) ? '_ROW_' : '_KEY_' . $key . '_') . $id;
        $result = $this->cachedData[$cacheKey] ?? $this->readCache($cacheKey);
        if ($result && !is_null($key)) {
            $result = $this->fetchRow($result);
            if ($result === false) {
                $this->flushRow($id, null, $cacheKey, $key);
            }
        }
        return $result;
    }

    /**
     * Fetch row data by sql
     * 
     * @param string $key
     * @param string $cacheKey
     * @return array
     */
    protected function fetchList($key, $cacheKey = null)
    {
        if (!is_null($cacheKey)) {
            $this->setDataCacheKey($cacheKey);
        }
        $cacheKey = $this->getDataCacheKey() . '_LIST_' . $key;
        $result = $this->cachedData[$cacheKey] ?? $this->readCache($cacheKey);
        return $result;
    }

    /**
     * Add or update row data
     * 
     * @param string $id
     * @param mixed $data
     * @param string $cacheKey
     * @param string $key
     */
    protected function flushRow($id, $data, $cacheKey, $key = null)
    {
        if (!is_null($cacheKey)) {
            $this->setDataCacheKey($cacheKey);
        }
        $cacheKey = $this->getDataCacheKey() . (is_null($key) ? '_ROW_' : '_KEY_' . $key . '_') . $id;
        if (is_null($data)) {
            $this->deleteCache($cacheKey);
        } else {
            $this->cachedData[$cacheKey] = $data;
            $this->writeCache($cacheKey);
        }
    }

    /**
     * Flush all list records
     * 
     * @param string $cacheKey
     */
    protected function flushList($cacheKey)
    {
        if (!is_null($cacheKey)) {
            $this->setDataCacheKey($cacheKey);
        }
        $cacheListKey = $this->getDataCacheKey() . '_LIST';
        $list = $this->readCache($cacheListKey);
        if ($list) {
            foreach ($list as $key => $value) {
                $this->deleteCache($key);
            }
            $this->deleteCache($cacheListKey);
        }
    }

    /**
     * Add or update a list record
     * 
     * @param string $key
     * @param array $list
     * @param string $cacheKey
     */
    protected function addCacheList($key, $list, $cacheKey)
    {
        if (!is_null($cacheKey)) {
            $this->setDataCacheKey($cacheKey);
        }
        $cacheKey = $this->getDataCacheKey() . '_LIST_' . $key;
        $this->cachedData[$cacheKey] = $list;
        $this->writeCache($cacheKey);
        $cacheListKey = $this->getDataCacheKey() . '_LIST';
        if (!$this->readCache($cacheListKey)) {
            $this->cachedData[$cacheListKey] = [$cacheKey => 1];
        } else {
            $this->cachedData[$cacheListKey][$cacheKey] = 1;
        }
        $this->writeCache($cacheListKey);
    }

    protected function addCacheAlias($key, $id, $cacheKey)
    {
        if (!is_null($cacheKey)) {
            $this->setDataCacheKey($cacheKey);
        }
        $cacheKey = $this->getDataCacheKey() . '_ROW_' . $key;
        $this->cachedData[$cacheKey] = $id;
        $this->writeCache($cacheKey);
    }

}
