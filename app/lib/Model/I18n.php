<?php

namespace Seahinet\Lib\Model;

use BadMethodCallException;
use Zend\Db\TableGateway\TableGateway;

class I18n
{

    use \Seahinet\Lib\Traits\Container;

    public function load($part, $id = '')
    {
        $cache = $this->getContainer()->get('cache');
        $result = $cache->fetch($part . $id, 'I18N_');
        if (!$result) {
            $tableGateway = new TableGateway('i18n_' . $part, $this->getContainer()->get('dbAdapter'));
            $select = $tableGateway->getSql()->select();
            $select->join('i18n_' . $part . '_name', $part . '_id=id', ['name', 'locale'], 'left');
            if ($id) {
                $select->where(['parent_id' => $id]);
            }
            $resultSet = $tableGateway->selectWith($select)->toArray();
            $result = [];
            foreach ($resultSet as $item) {
                if (isset($result[$item['id']])) {
                    $result[$item['id']]['name'][$item['locale']] = $item['name'];
                } else {
                    $result[$item['id']] = new I18n\Item($item);
                    $result[$item['id']]['name'] = [$item['locale'] => $item['name']];
                    unset($result[$item['id']]['locale']);
                }
            }
            $cache->save($part . $id, $result, 'I18N_');
        }
        return $result;
    }

    public function __call($name, $arguments)
    {
        if (in_array($name, ['getCountry', 'getCountries'])) {
            return $this->load('country');
        } else if (in_array($name, ['getRegion', 'getRegions', 'getState', 'getStates', 'getProvience', 'getProviences'])) {
            return $this->load('region', count($arguments) ? $arguments[0] : '');
        } else if (in_array($name, ['getCity', 'getCities'])) {
            return $this->load('city', count($arguments) ? $arguments[0] : '');
        } else if (in_array($name, ['getCounty', 'getCounties'])) {
            return $this->load('county', count($arguments) ? $arguments[0] : '');
        } else {
            throw new BadMethodCallException('Call to undefined method: ' . $name);
        }
    }

}
