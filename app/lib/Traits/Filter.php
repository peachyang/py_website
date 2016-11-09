<?php

namespace Seahinet\Lib\Traits;

use Seahinet\Lib\Model\Eav\Attribute;
use Seahinet\Lib\Model\Collection\Eav\Collection;

/**
 * Filter Collection
 */
trait Filter
{

    /**
     * @param \Zend\Db\Sql\Select|\Seahinet\Lib\Model\AbstractCollection $select
     * @param array $condition
     * @param array $skip
     * @param callable $extra
     */
    protected function filter($select, $condition = [], $skip = [], $extra = null)
    {
        if (!isset($skip['limit'])) {
            if (isset($condition['limit']) && $condition['limit'] === 'all') {
                $select->reset('limit')->reset('offset');
            } else {
                $limit = $condition['limit'] ?? 20;
                if (isset($condition['page'])) {
                    $select->offset(($condition['page'] - 1) * $limit);
                    unset($condition['page']);
                }
                $select->limit((int) $limit);
            }
            unset($condition['limit']);
        }
        if (!isset($skip['order'])) {
            if (isset($condition['asc'])) {
                $select->order((strpos($condition['asc'], ':') ?
                                str_replace(':', '.', $condition['asc']) :
                                $condition['asc']) . ' ASC');
                unset($condition['asc'], $condition['desc']);
            } else if (isset($condition['desc'])) {
                $select->order((strpos($condition['desc'], ':') ?
                                str_replace(':', '.', $condition['desc']) :
                                $condition['desc']) . ' DESC');
                unset($condition['desc']);
            }
        }
        if (!empty($condition)) {
            foreach ($condition as $key => $value) {
                if (trim($value) === '') {
                    unset($condition[$key]);
                } else if (strpos($key, ':')) {
                    if (strpos($value, '%') !== false) {
                        $select->where->like(str_replace(':', '.', $key), $value);
                    } else {
                        $condition[str_replace(':', '.', $key)] = $value;
                    }
                    unset($condition[$key]);
                } else if (strpos($value, '%') !== false) {
                    $select->where->like($key, $value);
                    unset($condition[$key]);
                } else if ($select instanceof Collection) {
                    $attribute = new Attribute;
                    $attribute->load($key, 'code');
                    if (in_array($attribute->offsetGet('input'), ['checkbox', 'multiselect'])) {
                        $select->where('(' . $key . ' LIKE \'' . $value . ',%\' OR '
                                . $key . ' LIKE \'%,' . $value . '\' OR '
                                . $key . ' LIKE \'%,' . $value . ',%\' OR '
                                . $key . ' = \'' . $value . '\')');
                        unset($condition[$key]);
                    }
                }
            }
            if (is_callable($extra)) {
                $extra($select, $condition);
            }
            $select->where($condition);
        }
    }

}
