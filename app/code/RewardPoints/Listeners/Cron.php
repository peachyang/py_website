<?php

namespace Seahinet\RewardPoints\Listeners;

use Exception;
use Swift_SwiftException;
use Seahinet\Customer\Model\Collection\Customer as CustomerCollection;
use Seahinet\Email\Model\Template;
use Seahinet\RewardPoints\Model\Collection\Record as Collection;
use Seahinet\RewardPoints\Model\Record;
use Zend\Db\Sql\Expression;

class Cron
{

    use \Seahinet\Lib\Traits\Container;

    public function schedule()
    {
        $config = $this->getContainer()->get('config');
        if ($config['rewardpoints/general/enable'] && ($points = $config['rewardpoints/gathering/birthday'])) {
            $from = $config['email/customer/sender_email'] ?: $config['default/sender_email'];
            $template = $config['rewardpoints/notifications/birthday'];
            if ($template) {
                $template = new Template;
                $template->load($template, 'code');
            }
            try {
                $collection = new CustomerCollection;
                $collection->where(['status' => 1])
                        ->where->greaterThanOrEqualTo('birthday', date('Y-m-d 0:0:0'))
                        ->lessThanOrEqualTo('birthday', date('Y-m-d 23:59:59'));
                $collection->load(true, true);
                $mailer = $this->getContainer()->get('mailer');
                foreach ($collection as $customer) {
                    $model = new Record([
                        'customer_id' => $customer['id'],
                        'count' => $points,
                        'comment' => 'Birthday Present',
                        'status' => 1
                    ]);
                    $model->save();
                    if ($template && $template->getId()) {
                        $mailer->send($template->getMessage($customer)->addFrom($from)->addTo($customer['email'], $customer['username']));
                    }
                }
            } catch (Swift_SwiftException $e) {
                $this->getContainer()->logException($e);
            } catch (Exception $e) {
                
            }
        }
    }

    private function getExpiredCount($record)
    {
        $collection = new Collection;
        $collection->columns(['customer_id', 'amount' => new Expression('sum(count)')])
                ->where([
                    'status' => 1,
                    'customer_id' => $record['customer_id']
                ])->group('customer_id')
                ->where->lessThan('count', 0)
                ->greaterThanOrEqualTo('id', $record['id']);
        $collection->load(false, true);
        $amount = count($collection) ? $collection[0]['amount'] : 0;
        return $record['count'] + $amount;
    }

    public function expiration()
    {
        $config = $this->getContainer()->get('config');
        if ($config['rewardpoints/general/enable'] && ($days = (int) $config['rewardpoints/gathering/expiration'])) {
            $date = date('Y-m-d H:i:s', strtotime('-' . $days . 'days'));
            $collection = new Collection;
            $collection->where(['status' => 1])
                    ->limit(100)
                    ->where->greaterThan('count', 0)
                    ->lessThanOrEqualTo('created_at', $date);
            foreach ($collection->load(false, true) as $item) {
                if (($expired = $this->getExpiredCount($item)) > 0) {
                    $record = new Record([
                        'customer_id' => $item['customer_id'],
                        'count' => 0 - $expired,
                        'comment' => 'Expiration',
                        'status' => 1
                    ]);
                    $record->save();
                }
            }
        }
    }

}
