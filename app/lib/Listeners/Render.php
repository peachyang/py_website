<?php

namespace Seahinet\Lib\Listeners;

use Seahinet\Lib\ViewModel\AbstractViewModel;

/**
 * Listen render event
 */
class Render implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container;

    public function render($event)
    {
        $response = $event['response'];
        if (!is_object($response)) {
            $data = $response;
            if (is_string($response)) {
                $response = $this->getContainer()->get('response');
                $response->getBody()->write($data);
            } else if (is_array($response)) {
                $callback = $this->getContainer()->get('request')->getQuery('callback');
                $response = $this->getContainer()->get('response');
                if ($callback) {
                    $response->withHeader('Content-Type', 'application/javascript; charset=UTF-8')
                            ->getBody()->write($callback . '(' . json_encode($data) . ');');
                } else {
                    $response->withHeader('Content-Type', 'application/json; charset=UTF-8')
                            ->getBody()->write(json_encode($data));
                }
            }
        } else if ($response instanceof AbstractViewModel) {
            $rendered = $response->render();
            $response = $this->getContainer()->get('response');
            $response->getBody()->write($rendered);
        }
    }

}
