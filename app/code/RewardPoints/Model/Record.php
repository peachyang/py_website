<?php

namespace Seahinet\RewardPoints\Model;

use Seahinet\Customer\Model\Customer;
use Seahinet\Email\Model\Collection\Template as TemplateCollection;
use Seahinet\Email\Model\Template as TemplateModel;
use Seahinet\Lib\Model\{
    AbstractModel,
    Language
};
use Seahinet\Sales\Model\Order;
use Swift_TransportException;

class Record extends AbstractModel
{

    use \Seahinet\RewardPoints\Traits\Recalc,
        \Seahinet\Lib\Traits\Translate;

    protected function construct()
    {
        $this->init('reward_points', 'id', ['id', 'customer_id', 'order_id', 'count', 'comment', 'status']);
    }

    public function getCustomer()
    {
        if (!empty($this->storage['customer_id'])) {
            $customer = new Customer;
            $customer->load($this->storage['customer_id']);
            if ($customer->getId()) {
                return $customer;
            }
        }
        return [];
    }

    public function getOrder()
    {
        if (!empty($this->storage['order_id'])) {
            $order = new Order;
            $order->load($this->storage['order_id']);
            if ($order->getId()) {
                return $order;
            }
        }
        return null;
    }

    protected function afterSave()
    {
        if ($this->storage['status']) {
            $this->recalc($this->storage['customer_id']);
            $config = $this->getContainer()->get('config');
            try {
                $customer = $this->getCustomer();
                if ($customer) {
                    $collection = new TemplateCollection;
                    $collection->join('email_template_language', 'email_template_language.template_id=email_template.id', [], 'left')
                            ->where([
                                'code' => $this->storage['comment'] === 'Birthday Present' ?
                                        $config['rewardpoints/notifications/birthday'] :
                                        ($this->storage['comment'] === 'Expiration' ?
                                                $config['rewardpoints/notifications/expiring'] :
                                                $config['rewardpoints/notifications/updated']),
                                'language_id' => $customer['language_id']
                    ]);
                    $language = new Language;
                    $language->load($customer['language_id']);
                    $mailer = $this->getContainer()->get('mailer');
                    $mailer->send((new TemplateModel($collection[0]))
                                    ->getMessage([
                                        'type' => $this->translate($this->storage['count'] > 0 ? 'gathered' : 'used', [], 'rewardpoints', $language['code']),
                                        'points' => abs($this->storage['count']),
                                        'balance' => $customer['rewardpoints'],
                                        'username' => $customer['username']
                                    ])
                                    ->addFrom($config['email/customer/sender_email'] ?: $config['email/default/sender_email'], $config['email/customer/sender_name'] ?: $config['email/default/sender_name'])
                                    ->addTo($customer['email'], $customer['username']));
                }
            } catch (Swift_TransportException $e) {
                $this->getContainer()->get('log')->logException($e);
            }
        }
        parent::afterSave();
    }

}
