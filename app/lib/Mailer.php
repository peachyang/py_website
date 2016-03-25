<?php

namespace Seahinet\Lib;

use Seahinet\Lib\Traits\Container;
use Swift_Mailer;

class Mailer extends Swift_Mailer
{

    use Container;

    public static $ALLOWED_TRANSPORTATION = [
        'Swift_SmtpTransport' => 'SMTP',
        'Swift_SendmailTransport' => 'Sendmail',
        'Swift_MailTransport' => 'Mail'
    ];
    
    protected $SMTPParams = [
        'host' => 'localhost',
        'port' => 25,
        'security' => null
    ];
    
    protected $SendmailParams = [
        'command' => '/usr/sbin/sendmail -bs'
    ];
    
    protected $MailParams = [
        'extra' => '-f%s'
    ];

    public function __construct()
    {
        $config = $this->getContainer()->get('config');
        $transport = call_user_func_array($config['mail']['transport'] . '::newInstance', ${static::$ALLOWED_TRANSPORTATION[$config['mail']['transport']] . 'Params'} + $config['mail']['params']);
        if (static::$ALLOWED_TRANSPORTATION[$config['mail']['transport']] === 'SMTP') {
            $transport->setUsername($config['mail']['params']['username']);
            $transport->setPassword($config['mail']['params']['password']);
        }
        parent::__construct($transport);
    }

}
