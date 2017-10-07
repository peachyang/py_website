<?php

namespace Seahinet\Oauth\Listeners;

use Exception;
use Seahinet\Lib\Listeners\ListenerInterface;
use Seahinet\Lib\Session\Segment;
use Seahinet\Oauth\Model\Client as Model;

class Client implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container;

    public function bind($e)
    {
        if ($this->getContainer()->get('request')->getPost('use_oauth', false)) {
            $segment = new Segment('oauth');
            $server = $segment->get('server', false);
            $openId = $segment->get('open_id', false);
            $model = $e['model'];
            if (($id = $model->getId()) && $server && $openId) {
                $client = new Model;
                try {
                    $client->setData([
                        'customer_id' => $id,
                        'oauth_server' => $server,
                        'open_id' => $openId
                    ])->save();
                    $segment->offsetUnset('server');
                    $segment->offsetUnset('open_id');
                } catch (Exception $e) {
                    
                }
            }
        }
    }

}
