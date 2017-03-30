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
     * Mailer configuration
     * 
     * @var array|Config
     */
    protected $config;

    /**
     * @param array|Container $container
     */
    public function __construct($container = null)
    {
        if ($container instanceof Container) {
            $this->setContainer($container);
            $this->config = $this->getContainer()->get('config');
        } else {
            $this->config = $container;
        }
        $transport = call_user_func_array($this->config['email/transport/service'] . '::newInstance', $this->{static::$ALLOWED_TRANSPORTATION[$this->config['email/transport/service']] . 'Params'});
        if (static::$ALLOWED_TRANSPORTATION[$this->config['email/transport/service']] === 'SMTP') {
            $transport->setHost($this->config['email/transport/host']);
            $transport->setPort($this->config['email/transport/port']);
            if ($encyption = $this->config['email/transport/security']) {
                $transport->setEncryption($encyption);
            }
            $transport->setAuthMode($this->config['email/transport/auth']);
            $transport->setUsername($this->config['email/transport/username']);
            $transport->setPassword($this->config['email/transport/password']);
        }
        parent::__construct($transport);
    }

    /**
     * {@inhertDoc}
     */
    public function send(\Swift_Mime_Message $message, &$failedRecipients = null)
    {
        return $this->config['email/transport/enable'] ?
                parent::send($message, $failedRecipients) : true;
    }

}
