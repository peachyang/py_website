<?php

namespace Seahinet\Lib\Listeners;

use Seahinet\Lib\ViewModel\AbstractViewModel;

class Render implements ListenerInterface
{

    use \Seahinet\Lib\Traits\Container;

    public function render($event)
    {
        $response = $event['response'];
        if (!is_object($response)) {
            if (is_string($response)) {
                $response = $this->getContainer()->get('response');
                $response->getBody()->write($response);
            } else if (is_array($response)) {
                $response = $this->getContainer()->get('response');
                $response->withHeader('Content-Type','application/json; charset=UTF-8')->getBody()->write(json_encode($response));
            }
        } else if ($response instanceof AbstractViewModel) {
            $response = $this->getContainer()->get('response');
            if ($response->getCacheKey()) {
                $cache = $this->getContainer()->get('cache');
                $rendered = $cache->fetch('VIEWMODEL_RENDERED_' . $response->getCacheKey());
                if (!$rendered) {
                    $rendered = $response->render();
                    $cache->save('VIEWMODEL_RENDERED_' . $response->getCacheKey(), $rendered);
                }
            } else {
                $rendered = $response->render();
            }
            $response->getBody()->write($rendered);
        }
    }

}
