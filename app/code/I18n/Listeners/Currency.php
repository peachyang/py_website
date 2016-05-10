<?php

namespace Seahinet\I18n\Listeners;

use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\I18n\Model\Currency as Model;
use Zend\Db\Sql\Predicate\NotIn;

class Currency implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container,
        \Seahinet\Lib\Traits\DB,
        \Seahinet\Lib\Traits\DataCache,
        \Seahinet\Lib\Traits\Translate,
        \Seahinet\I18n\Traits\Currency;

    public function afterSave($event)
    {
        try {
            $this->getTableGateway('i18n_currency')->delete(new NotIn('code', $event['value']));
            foreach ($event['value'] as $code) {
                $this->upsert(['code' => $code], ['code' => $code]);
            }
            $this->flushList('i18n_currency\\');
        } catch (\Exception $e) {
            
        }
    }

    public function schedule()
    {
        $config = $this->getContainer()->get('config');
        $base = $config['i18n/currency/base'];
        $collection = $config['i18n/currency/enabled'];
        if (is_string($collection)) {
            $collection = explode(',', $collection);
        }
        return $this->sync($collection, $base)['message'][0]['message'];
    }

}
