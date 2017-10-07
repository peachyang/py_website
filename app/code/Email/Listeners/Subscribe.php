<?php

namespace Seahinet\Email\Listeners;

use Seahinet\Email\Model\Subscriber;
use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Listeners\ListenerInterface;

class Subscribe implements ListenerInterface
{

    public function subscribe($event)
    {
        $data = $event['data'];
        $subscriber = new Subscriber;
        $subscriber->load($data['email'], 'email');
        if (empty($data['subscribe'])) {
            if ($subscriber->getId()) {
                $subscriber->unsubscribe();
            }
        } else {
            $subscriber->setData([
                'email' => $data['email'],
                'language_id' => Bootstrap::getLanguage()->getId(),
                'status' => 1
            ])->save();
        }
    }

}
