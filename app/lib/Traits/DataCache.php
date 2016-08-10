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
            if (!$this->cachedData[$cacheKey]) {
                $this->cachedData[$cacheKey] = ['row' => [], 'key' => [], 'list' => []];
            }
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
     * Fetch a row data by key
     * 
     * @param int|string $id
     * @param string $key
     * @param string $cacheKey
     * @return mixed
     */
    protected function fetchRow($id, $key = null, $cacheKey = null)
    {
        if (!is_null($cacheKey) && !isset($this->cachedData[$cacheKey])) {
            $this->readCache($cacheKey);
        }
        if(is_object($id)){
            $id = $id['id'];
        }
        if (is_null($key)) {
            return isset($this->cachedData[$cacheKey]['row'][$id]) ?
                    $this->cachedData[$cacheKey]['row'][$id] : false;
        } else if (isset($this->cachedData[$cacheKey]['key'][$key . '=' . $id])) {
            $result = $this->fetchRow($this->cachedData[$cacheKey]['key'][$key . '=' . $id]);
            if ($result === false) {
                unset($this->cachedData[$cacheKey]['key'][$key . '=' . $id]);
                $this->writeCache($cacheKey);
            }
            return $result;
        } else {
            return false;
        }
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
        if (!is_null($cacheKey) && !isset($this->cachedData[$cacheKey])) {
            $this->readCache($cacheKey);
        }
        return isset($this->cachedData[$cacheKey]['list'][$key]) ? $this->cachedData[$cacheKey]['list'][$key] : false;
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
        if (!isset($this->cachedData[$cacheKey])) {
            $this->readCache($cacheKey);
        }
        if (is_null($data)) {
            if (is_null($key)) {
                unset($this->cachedData[$cacheKey]['row'][$id]);
            } else if (isset($this->cachedData[$cacheKey]['key'][$key . '=' . $id])) {
                unset($this->cachedData[$cacheKey]['row'][$this->cachedData[$cacheKey]['key'][$key . '=' . $id]]);
                unset($this->cachedData[$cacheKey]['key'][$key . '=' . $id]);
            }
        } else {
            if (is_null($key)) {
                $this->cachedData[$cacheKey]['row'][$id] = $data;
            } else if (isset($this->cachedData[$cacheKey]['key'][$key . '=' . $id])) {
                $this->cachedData[$cacheKey]['row'][$this->cachedData[$cacheKey]['key'][$key . '=' . $id]] = $data;
            }
        }
        $this->writeCache($cacheKey);
    }

    /**
     * Flush all list records
     * 
     * @param string $cacheKey
     */
    protected function flushList($cacheKey)
    {
        if (!isset($this->cachedData[$cacheKey])) {
            $this->readCache($cacheKey);
        }
        $this->cachedData[$cacheKey]['list'] = [];
        $this->writeCache($cacheKey);
    }

    /**
     * Add or update a list record
     * 
     * @param string $key
     * @param array $list
     * @param string $cacheKey
     */
    protected function addCacheList($key, array $list, $cacheKey)
    {
        if (!isset($this->cachedData[$cacheKey])) {
            $this->readCache($cacheKey);
        }
        if (!empty($list)) {
            $this->cachedData[$cacheKey]['list'][$key] = $list;
            $this->writeCache($cacheKey);
        }
    }

    protected function addCacheAlias($key, $id, $cacheKey)
    {
        if (!isset($this->cachedData[$cacheKey])) {
            $this->readCache($cacheKey);
        }
        $this->cachedData[$cacheKey]['key'][$key] = $id;
        $this->writeCache($cacheKey);
    }

}
