<?php

namespace Seahinet\Log\ViewModel;

use Seahinet\Lib\Bootstrap;
use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\ViewModel\AbstractViewModel;

class Visitor extends AbstractViewModel
{

    public function render()
    {
        $customer = new Segment('customer');
        $config = $this->getConfig();
        if ($config['log/enabled'] &&
                ($config['log/target'] == 0 || $customer->get('hasLoggedIn')) &&
                ($config['log/dnt_check'] == 0 || !$this->getRequest()->getHeader('DNT'))) {
            $catalog = new Segment('catalog');
            $result = '
        <script>(function() {
var os = document.createElement("script");
os.src = "' . ($config['log/url'] ?: ($this->getBaseUrl('log/') . str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode((
                                    $customer->get('hasLoggedIn') ? $customer->get('customer')->getId() : 'n') . '-' .
                                    Bootstrap::getStore()->getId() . '-' .
                                    $catalog->get('product_id', 'n'))
                    ))) . '.js";
var s = document.getElementsByTagName("script")[0];
s.parentNode.insertBefore(os, s);
})();</script>';
            $catalog->offsetUnset('product_id');
        }
        return $result ?? '';
    }

}
