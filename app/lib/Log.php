<?php

namespace Seahinet\Lib;

use BadMethodCallException;
use Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Log
{

    /**
     * @var Logger 
     */
    protected static $logger = null;

    /**
     * @param array|Container $config
     */
    public function __construct($config = [])
    {
        if ($config instanceof Container) {
            $config = $config->get('config')['config']['log'];
        }
        if (!empty($config)) {
            $this->setLogger($config);
        }
    }

    public function __call($name, $arguments)
    {
        if (is_callable([static::$logger, $name])) {
            return call_user_func_array([static::$logger, $name], $arguments);
        } else {
            throw new BadMethodCallException('Call to undefined method: ' . $name);
        }
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        if (is_null(static::$logger)) {
            static::$logger = new Logger('default');
            static::$logger->pushHandler(new StreamHandler(BP . 'var/log/debug.log', Logger::DEBUG, false, 0644));
            static::$logger->pushHandler(new StreamHandler(BP . 'var/log/exception.log', Logger::ERROR, false, 0644));
        }
        return static::$logger;
    }

    /**
     * @param array $config
     */
    public function setLogger(array $config = [])
    {
        $name = isset($config['name']) ? $config['name'] : 'default';
        $handlers = isset($config['handlers']) ? $config['handlers'] : [
            new StreamHandler(BP . 'var/log/debug.log', Logger::DEBUG, false, 0644),
            new StreamHandler(BP . 'var/log/exception.log', Logger::ERROR, false, 0644)
        ];
        $processors = isset($config['processors']) ? $config['processors'] : [];
        static::$logger = new Logger($name, $handlers, $processors);
    }

    /**
     * @uses Logger::error
     * @param Exception $e
     */
    public function logException(Exception $e)
    {
        $this->getLogger()->error($e->getMessage(), $e->getTrace());
    }

    /**
     * @uses Logger::addRecord
     * @param string $message
     * @param int $level
     */
    public function log($message = '', $level = Logger::DEBUG)
    {
        if (is_string($level) && defined(Logger::$level)) {
            $level = Logger::$level;
        } else {
            $level = Logger::DEBUG;
        }
        $this->getLogger()->addRecord($level, $message);
    }

}
