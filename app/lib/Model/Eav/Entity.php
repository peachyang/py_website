<?php

namespace Seahinet\Lib\Model\Eav;

use Exception;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Exception\BadIndexerException;
use Seahinet\Lib\Model\AbstractModel;
use Seahinet\Lib\Model\Collection\Eav\Attribute as AttributeCollection;
use Seahinet\Lib\Model\Collection\Language;
use Zend\Db\Adapter\Exception\InvalidQueryException;
use Zend\Db\TableGateway\TableGateway;

abstract class Entity extends AbstractModel
{

    const ENTITY_TYPE = '';

    protected $entityTable = '';
    protected $valueTablePrefix = '';
    protected $languageId = 0;
    protected $attributes = [];

    public function __construct($languageId = 0, $input = array())
    {
        $this->storage = $input;
        if ($languageId) {
            $this->languageId = $languageId;
        } else {
            $this->languageId = Bootstrap::getLanguage()->getId();
        }
        $this->construct();
    }

    protected function init($primaryKey = 'id', $columns = ['id', 'type_id', 'attribute_set_id', 'store_id', 'increment_id', 'status'], $null = null)
    {
        $this->cacheKey = static::ENTITY_TYPE;
        $this->columns = $columns;
        $this->primaryKey = $primaryKey;
    }

    public function load($id, $key = null)
    {
        if (!$this->isLoaded) {
            try {
                $this->beforeLoad(null);
                if ($result = $this->loadFromCache($id, $key)) {
                    $this->afterLoad($result);
                } else if ($result = $this->loadFromIndexer($id, $key)) {
                    $this->afterLoad($result);
                    $this->flushRow($this->languageId . '-' . $this->storage[$this->primaryKey], $this->storage, $this->getCacheKey());
                    if ($key !== $this->primaryKey) {
                        $this->addCacheAlias($key . '=' . $id, $this->storage[$this->primaryKey], $this->getCacheKey());
                    }
                }
            } catch (BadIndexerException $e) {
                if ($result = $this->loadFromDb()) {
                    $this->afterLoad($result);
                    $this->flushRow($this->languageId . '-' . $this->storage[$this->primaryKey], $this->storage, $this->getCacheKey());
                    if ($key !== $this->primaryKey) {
                        $this->addCacheAlias($key . '=' . $id, $this->storage[$this->primaryKey], $this->getCacheKey());
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
        return $this;
    }

    protected function loadFromCache($id, $key = null)
    {
        if (is_null($key) || $key === $this->primaryKey) {
            $key = $this->primaryKey;
            $result = $this->fetchRow($this->languageId . '-' . $id, null, $this->getCacheKey());
        } else {
            $result = $this->fetchRow($this->languageId . '-' . $id, $this->languageId . '-' . $key, $this->getCacheKey());
        }
        return $result;
    }

    protected function loadFromIndexer($id, $key = null)
    {
        return $this->getContainer()->get('indexer')->select(static::ENTITY_TYPE, $this->languageId, [(is_null($key) ? $this->primaryKey : $key) => (string) $id]);
    }

    protected function getEntityTable()
    {
        if (!$this->entityTable) {
            $tableGateway = new TableGateway('eav_entity_type', $this->getContainer()->get('dbAdapter'));
            $select = $tableGateway->getSql()->select();
            $select->where(['eav_entity_type.code' => static::ENTITY_TYPE]);
            $result = $tableGateway->selectWith($select)->toArray();
            if (count($result)) {
                $this->entityTable = $result[0]['entity_table'];
                $this->valueTablePrefix = $result[0]['value_table_prefix'];
                $this->getTableGateway($this->entityTable);
            }
        }
        return $this->entityTable;
    }

    protected function loadFromDb()
    {
        $select = $this->getTableGateway($this->getEntityTable())->getSql()->select();
        $select->join('eav_attribute', 'eav_attribute.type_id=' . $this->entityTable . '.type_id', ['attr' => 'code', 'type', 'is_required', 'default_value', 'is_unique'], 'left')
                ->join($this->valueTablePrefix . '_int', 'eav_attribute.id=' . $this->valueTablePrefix . '_int.attribute_id', ['value_int' => 'value'], 'left')
                ->join($this->valueTablePrefix . '_varchar', 'eav_attribute.id=' . $this->valueTablePrefix . '_varchar.attribute_id', ['value_varchar' => 'value'], 'left')
                ->join($this->valueTablePrefix . '_datetime', 'eav_attribute.id=' . $this->valueTablePrefix . '_datetime.attribute_id', ['value_datetime' => 'value'], 'left')
                ->join($this->valueTablePrefix . '_blob', 'eav_attribute.id=' . $this->valueTablePrefix . '_blob.attribute_id', ['value_blob' => 'value'], 'left')
                ->join($this->valueTablePrefix . '_text', 'eav_attribute.id=' . $this->valueTablePrefix . '_text.attribute_id', ['value_text' => 'value'], 'left')
                ->join($this->valueTablePrefix . '_decimal', 'eav_attribute.id=' . $this->valueTablePrefix . '_decimal.attribute_id', ['value_decimal' => 'value'], 'left');
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
                $items[$record['id']] = [
                    'id' => $record['id'],
                    'store_id' => $record['store_id'],
                    'increment_id' => $record['increment_id'],
                    'status' => $record['status'],
                    'created_at' => $record['created_at']
                ];
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

    public function save($constraint = [], $insertForce = false)
    {
        $isUpdate = !$insertForce && $this->getId();
        try {
            if ($isUpdate || $this->isNew) {
                $this->beginTransaction();
                $this->beforeSave();
                $columns = $this->prepareColumns();
                $attributes = $this->prepareAttributes();
                $tableGateway = $this->getTableGateway($this->getEntityTable());
                if ($isUpdate) {
                    $tableGateway->update($columns, ['id' => $this->getId()]);
                } else {
                    $tableGateway->insert($columns);
                    $this->setId($tableGateway->getLastInsertValue());
                }
                $adapter = $this->getContainer()->get('dbAdapter');
                $tableGateways = [];
                $languages = new Language;
                $languages->columns(['id']);
                $index = [];
                foreach ($this->attributes as $attr) {
                    if (!isset($tableGateways[$attr['type']])) {
                        $tableGateways[$attr['type']] = new TableGateway($this->valueTablePrefix . '_' . $attr['type'], $adapter);
                    }
                    if (is_array($attributes[$attr['code']])) {
                        foreach ($attributes[$attr['code']] as $id => $value) {
                            if (!isset($index[$id])) {
                                $index[$id] = [];
                            }
                            $index[$id][$attr['code']] = $value;
                            if ($isUpdate) {
                                $this->upsert(['value' => $value], ['language_id' => $id, 'entity_id' => $this->getId(), 'attribute_id' => $attr['id']], $tableGateways[$attr['type']]);
                            } else {
                                $this->insert(['value' => $value, 'language_id' => $id, 'entity_id' => $this->getId(), 'attribute_id' => $attr['id']], $tableGateways[$attr['type']]);
                            }
                        }
                    } else {
                        foreach ($languages as $language) {
                            if (!isset($index[$language['id']])) {
                                $index[$language['id']] = [];
                            }
                            $index[$language['id']][$attr['code']] = $attributes[$attr['code']];
                            if ($isUpdate) {
                                $this->upsert(['value' => $attributes[$attr['code']]], ['language_id' => $language['id'], 'entity_id' => $this->getId(), 'attribute_id' => $attr['id']], $tableGateways[$attr['type']]);
                            } else {
                                $this->insert(['value' => $attributes[$attr['code']], 'language_id' => $language['id'], 'entity_id' => $this->getId(), 'attribute_id' => $attr['id']], $tableGateways[$attr['type']]);
                            }
                        }
                    }
                }
                foreach ($languages as $language) {
                    if ($isUpdate) {
                        $this->getContainer()->get('indexer')->update(static::ENTITY_TYPE, $language['id'], $columns + $index[$language['id']], [$this->primaryKey => $this->getId()]);
                    } else {
                        $this->getContainer()->get('indexer')->insert(static::ENTITY_TYPE, $language['id'], [$this->primaryKey => $this->getId()] + $columns + $index[$language['id']]);
                    }
                }
                $this->afterSave();
                $this->commit();
                if ($isUpdate) {
                    $this->flushRow($this->languageId . '-' . $this->getId(), null, $this->getCacheKey());
                }
                $this->flushList($this->getCacheKey());
            }
        } catch (InvalidQueryException $e) {
            $this->getContainer()->get('log')->logException($e);
            $this->rollback();
            throw $e;
        } catch (Exception $e) {
            $this->getContainer()->get('log')->logException($e);
            throw $e;
        }
        return $this;
    }

    public function remove()
    {
        if ($this->getId()) {
            try {
                $this->beforeRemove();
                $this->getTableGateway($this->getEntityTable())->delete([$this->primaryKey => $this->getId()]);
                $languages = new Language;
                $languages->columns(['id']);
                foreach ($languages as $language) {
                    $this->getContainer()->get('indexer')->delete(static::ENTITY_TYPE, $language['id'], [$this->primaryKey => $this->getId()]);
                }
                $this->flushRow($this->languageId . '-' . $this->getId(), null, $this->getCacheKey());
                $this->flushList($this->getCacheKey());
                $this->storage = [];
                $this->isLoaded = false;
                $this->isNew = true;
                $this->updatedColumns = [];
                $this->afterRemove();
            } catch (InvalidQueryException $e) {
                $this->getContainer()->get('log')->logException($e);
                if ($this->transaction) {
                    $this->rollback();
                }
                throw $e;
            } catch (Exception $e) {
                $this->getContainer()->get('log')->logException($e);
                throw $e;
            }
        }
    }

    protected function prepareAttributes()
    {
        if (empty($this->attributes)) {
            $this->attributes = new AttributeCollection;
            $this->attributes->withSet()->columns(['code', 'id', 'type'])
                    ->join('eav_entity_type', 'eav_attribute.type_id=eav_entity_type.id', [], 'left')
                    ->where(['eav_entity_type.code' => static::ENTITY_TYPE, 'attribute_set_id' => $this->storage['attribute_set_id']]);
        }
        $attrs = [];
        $this->attributes->walk(function($attr) use (&$attrs) {
            $attrs[] = $attr['code'];
        });
        $pairs = [];
        foreach ($this->storage as $key => $value) {
            if (in_array($key, $attrs) && ($this->isNew || in_array($key, $this->updatedColumns))) {
                $pairs[$key] = $value;
            }
        }
        return $pairs;
    }

}
