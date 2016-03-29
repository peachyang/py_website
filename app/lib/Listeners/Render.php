<?php

namespace Seahinet\Lib\Listeners;

use Seahinet\Lib\Http\Response;

class Render implements ListenerInterface
{

    public function render($event)
    {
        $response = $event['response'];
        if (is_object($response)) {
            if ($response instanceof Response) {
                
            }
        } else {
            if (is_string($response)) {
                
            } else if (is_array($response)) {
                
            }
        }
    }

}
