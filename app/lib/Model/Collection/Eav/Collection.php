<?php

namespace Seahinet\Lib\Model\Collection\Eav;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Model\AbstractCollection;
use Zend\Db\TableGateway\TableGateway;

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
                if ($result = $this->loadFromDb($id, $key)) {
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
        return $this->getContainer()->get('indexer')->select(static::ENTITY_TYPE, $this->languageId, $this->select);
    }

    protected function loadFromDb()
    {
        $tableGateway = new TableGateway('eav_entity_type', $this->getContainer()->get('dbAdapter'));
        $select = $tableGateway->getSql()->select();
        $select->where(['eav_entity_type.code' => static::ENTITY_TYPE]);
        $result = $tableGateway->selectWith($select)->toArray();
        if (count($result)) {
            $entityTable = $result[0]['entity_table'];
            $valueTablePrefix = $result[0]['value_table_prefix'];
        }
        $tableGateway = new TableGateway($entityTable, $this->getContainer()->get('dbAdapter'));
        $select = $tableGateway->getSql()->select();
        $select->join('eav_attribute', 'eav_attribute.type_id=' . $entityTable . '.type_id', ['attr' => 'code', 'type', 'is_required', 'default_value', 'is_unique'], 'left')
                ->join($valueTablePrefix . '_int', 'eav_attribute.id=' . $valueTablePrefix . '_int.attribute_id', ['value_int' => 'value'], 'left')
                ->join($valueTablePrefix . '_varchar', 'eav_attribute.id=' . $valueTablePrefix . '_varchar.attribute_id', ['value_varchar' => 'value'], 'left')
                ->join($valueTablePrefix . '_datetime', 'eav_attribute.id=' . $valueTablePrefix . '_datetime.attribute_id', ['value_datetime' => 'value'], 'left')
                ->join($valueTablePrefix . '_blob', 'eav_attribute.id=' . $valueTablePrefix . '_blob.attribute_id', ['value_blob' => 'value'], 'left')
                ->join($valueTablePrefix . '_text', 'eav_attribute.id=' . $valueTablePrefix . '_text.attribute_id', ['value_text' => 'value'], 'left')
                ->join($valueTablePrefix . '_decimal', 'eav_attribute.id=' . $valueTablePrefix . '_decimal.attribute_id', ['value_decimal' => 'value'], 'left');
        $items = [];
        try {
            $records = $this->getTableGateway()->selectWith($select)->toArray();
        } catch (InvalidQueryException $e) {
            $this->getContainer()->get('log')->logException($e);
            return [];
        } catch (Exception $e) {
            $this->getContainer()->get('log')->logException($e);
            return [];
        }
        foreach ($records as $record) {
            if (!isset($record['id']) || !$record['id']) {
                continue;
            }
            if (!isset($items[$record['id']])) {
                $items[$record['id']] = [];
                foreach (array_diff(array_keys($record), [
                    'type_id', 'attr', 'type', 'is_required', 'default_value',
                    'is_unique', 'code', 'entity_table', 'value_table_prefix',
                    'is_form', 'value_varchar', 'value_decimal', 'value_text',
                    'value_int', 'value_blob', 'value_datetime',
                    'language_varchar', 'language_decimal', 'language_text',
                    'language_int', 'language_blob', 'language_datetime'
                ]) as $key) {
                    $items[$record['id']][$key] = $record[$key];
                }
            }
            if ($record['attr']) {
                $items[$record['id']][$record['attr']] = $record['value_int']? : (
                        $record['value_varchar']? : (
                                $record['value_decimal']? : (
                                        $record['value_text']? : (
                                                $record['value_datetime']? :
                                                        $record['value_blob']
                                                ))));
            }
        }
        return array_values($items);
    }

}
