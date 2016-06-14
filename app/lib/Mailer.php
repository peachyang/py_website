<?php

namespace Seahinet\Lib;

use Swift_Mailer;

/**
 * Swift mailer factory
 */
class Mailer extends Swift_Mailer
{

    use \Seahinet\Lib\Traits\Container;

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
        $transport = call_user_func_array($config['email/transport/service'] . '::newInstance', $this->{static::$ALLOWED_TRANSPORTATION[$config['email/transport/service']] . 'Params'});
        if (static::$ALLOWED_TRANSPORTATION[$config['email/transport/service']] === 'SMTP') {
            $transport->setHost($config['email/transport/host']);
            $transport->setPort($config['email/transport/port']);
            if ($encyption = $config['email/transport/security']) {
                $transport->setEncryption($encyption);
            }
            $transport->setAuthMode($config['email/transport/auth']);
            $transport->setUsername($config['email/transport/username']);
            $transport->setPassword($config['email/transport/password']);
        }
        parent::__construct($transport);
    }

}
