<?php

namespace Seahinet\Customer\Listeners;

use Seahinet\Customer\Model\Customer;
use Seahinet\Customer\Model\Persistent as Model;
use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Lib\Session\Segment;

class Persistent implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container;

    public function validate()
    {
        $segment = new Segment('customer');
        if (!$segment->get('hasLoggedIn') && $key = $this->getContainer()->get('request')->getCookie('persistent')) {
            $persistent = new Model;
            $persistent->load($key, 'key');
            if ($persistent->offsetGet('updated_at') + 604800 < time()) {
                $customer = new Customer;
                $customer->load($persistent->offsetGet('customer_id'));
                $key = md5(random_bytes(32) . $customer->offsetGet('username'));
                $persistent->setData('key', $key)->save();
                $segment->set('hasLoggedIn', true)
                        ->set('customer', clone $customer);
                $this->getContainer()->get('response')->withCookie('persistent', ['value' => $key, 'path' => '/', 'expires' => time() + 604800]);
            }
        }
    }

}
