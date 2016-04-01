<?php

namespace Seahinet\Lib;

use Swift_Mailer;

/**
 * Swift mailer factory
 */
class Mailer extends Swift_Mailer
{

    use Traits\Container;

    /**
     * Allowed transportation class
     * 
     * @var array
     */
    public static $ALLOWED_TRANSPORTATION = [
        'Swift_SmtpTransport' => 'SMTP',
        'Swift_SendmailTransport' => 'Sendmail',
        'Swift_MailTransport' => 'Mail'
    ];

    /**
     * SMTP configuration
     * 
     * @var array 
     */
    protected $SMTPParams = [
        'host' => 'localhost',
        'port' => 25,
        'security' => null
    ];

    /**
     * Sendmail configuration
     * 
     * @var array 
     */
    protected $SendmailParams = [
        'command' => '/usr/sbin/sendmail -bs'
    ];

    /**
     * Mail configuration
     * 
     * @var array 
     */
    protected $MailParams = [
        'extra' => '-f%s'
    ];

    /**
     * @param array|Container $container
     */
    public function __construct($container = null)
    {
        if ($container instanceof Container) {
            $this->setContainer($container);
        }
        $config = $this->getContainer()->get('config');
        $transport = call_user_func_array($config['mail']['transport'] . '::newInstance', ${static::$ALLOWED_TRANSPORTATION[$config['mail']['transport']] . 'Params'} + $config['mail']['params']);
        if (static::$ALLOWED_TRANSPORTATION[$config['mail']['transport']] === 'SMTP') {
            $transport->setUsername($config['mail']['params']['username']);
            $transport->setPassword($config['mail']['params']['password']);
        }
        parent::__construct($transport);
    }

}
