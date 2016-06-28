<?php
namespace Seahinet\Oauth\Model\Collection;

use Seahinet\Lib\Model\AbstractCollection;
/**
 * Description of SoapUser
 *
 * @author lenovo
 */
class SoapUser extends AbstractCollection 
{
    protected function construct() {
        $this->init('oauth_soapuser');
    }
}
