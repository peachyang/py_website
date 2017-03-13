<?php

namespace Seahinet\Customer\Model\Api\Soap;

use Exception;
use Seahinet\Api\Model\Api\AbstractHandler;
use Seahinet\Customer\Model\Collection\Customer as Collection;
use Seahinet\Customer\Model\Customer as Model;
use Seahinet\Lib\Model\Collection\Eav\Attribute;

class Customer extends AbstractHandler
{

    use \Seahinet\Lib\Traits\Container;

    /**
     * @param string $sessionId
     * @param string $username
     * @param string $password
     * @return int
     */
    public function customerValid($sessionId, $username, $password)
    {
        $this->validateSessionId($sessionId, __FUNCTION__);
        $customer = new Model;
        return $customer->valid($username, $this->decryptData($password)) ? $customer->getId() : 0;
    }

    /**
     * @param string $sessionId
     * @param int $customerId
     * @return array
     */
    public function customerInfo($sessionId, $customerId)
    {
        $this->validateSessionId($sessionId, __FUNCTION__);
        $customer = new Model;
        $customer->load($customerId);
        $result = ['id' => $customer->getId()];
        $attributes = new Attribute;
        $attributes->join('eav_entity_type', 'eav_entity_type.id=eav_attribute.type_id', [], 'left')
                ->where(['eav_entity_type.code' => Model::ENTITY_TYPE])
        ->where->notEqualTo('input', 'password');
        $attributes->load(true, true);
        $attributes->walk(function($attribute) use (&$result, $customer) {
            $result[$attribute['code']] = $customer->offsetGet($attribute['code']);
        });
        return $this->response($result);
    }

    /**
     * @param string $sessionId
     * @param object $data
     * @return array
     */
    public function customerCreate($sessionId, $data)
    {
        $this->validateSessionId($sessionId, __FUNCTION__);
        $data = (array) $data;
        $config = $this->getContainer()->get('config');
        $attributes = new Attribute;
        $attributes->withSet()->where(['attribute_set_id' => $config['customer/registion/set']])
                ->where('(is_required=1 OR is_unique=1)')
                ->columns(['code', 'is_unique', 'type_id'])
                ->join('eav_entity_type', 'eav_attribute.type_id=eav_entity_type.id', [], 'right')
                ->where(['eav_entity_type.code' => Model::ENTITY_TYPE]);
        $unique = [];
        foreach ($attributes as $attribute) {
            if ($attribute['is_unique']) {
                $unique[] = $attribute['code'];
            }
        }
        $collection = new Collection;
        $collection->columns($unique);
        foreach ($unique as $code) {
            if (isset($data[$code])) {
                $collection->where([$code => $data[$code]], 'OR');
            }
        }
        if (count($collection)) {
            foreach ($collection as $item) {
                foreach ($unique as $code) {
                    if (isset($item[$code]) && $item[$code]) {
                        throw new Exception('The field ' . $code . ' has been used.');
                    }
                }
                break;
            }
        }
        $customer = new Model;
        $customer->setData([
            'id' => null,
            'attribute_set_id' => $config['customer/registion/set'],
            'group_id' => $config['customer/registion/group'],
            'type_id' => $attributes[0]['type_id'],
            'store_id' => 1,
            'language_id' => 1,
            'status' => 1
                ] + $data);
        $customer->save();
        if (!empty($data['subscribe'])) {
            $this->getContainer()->get('eventDispatcher')->trigger('subscribe', ['data' => $data]);
        }
        return $customer->getId();
    }

}
