<?php

namespace Seahinet\Email\Listeners;

use Seahinet\Email\Model\Subscriber;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Listeners\ListenerInterface;

class Password implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container;
    
    public function notify($event)
    {
        $customer = $event['model'];
        $data = $event['data'];
        $config = $this->getContainer()->get('config');
        if ($customer['modified_password']) {
            $collection = new TemplateCollection;
            $collection->join('email_template_language', 'email_template_language.template_id=email_template.id', [], 'left')
                    ->where([
                        'code' => $config['email/customer/modified_template'],
                        'language_id' => $customer['language_id']
            ]);
            $language = new Language;
            $language->load($customer['language_id']);
            $mailer = $this->getContainer()->get('mailer');
            $mailer->send((new TemplateModel($collection[0]))
                            ->getMessage([
                                'password' => $data['password'],
                                'username' => $customer['username']
                            ])
                            ->addFrom($config['email/customer/sender_email'] ?: $config['email/default/sender_email'], $config['email/customer/sender_name'] ?: $config['email/default/sender_name'])
                            ->addTo($customer['email'], $customer['username']));
        }
    }

}
