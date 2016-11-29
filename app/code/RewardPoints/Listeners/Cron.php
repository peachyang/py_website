<?php

namespace Seahinet\RewardPoints\Listeners;

use Exception;
use Swift_SwiftException;
use Seahinet\Customer\Model\Collection\Customer as Collection;
use Seahinet\Email\Model\Template;
use Seahinet\RewardPoints\Model\Record;

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
                $collection = new Collection;
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

}
