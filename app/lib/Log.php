<?php

namespace Seahinet\Lib;

use BadMethodCallException;
use Error;
use Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Logging service
 * 
 * @uses Logger
 * @see Psr\Log\LoggerInterface
 * @method emergency(string $message, array $context)
 * @method alert(string $message, array $context)
 * @method critical(string $message, array $context)
 * @method error(string $message, array $context)
 * @method warning(string $message, array $context)
 * @method notice(string $message, array $context)
 * @method info(string $message, array $context)
 * @method debug(string $message, array $context)
 */
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
            static::$logger->pushHandler(new StreamHandler(BP . 'var/log/debug.log', Logger::DEBUG, false, 0640));
            static::$logger->pushHandler(new StreamHandler(BP . 'var/log/exception.log', Logger::ERROR, false, 0640));
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
            new StreamHandler(BP . 'var/log/debug.log', Logger::DEBUG, false, 0640),
            new StreamHandler(BP . 'var/log/exception.log', Logger::ERROR, false, 0640)
        ];
        $processors = isset($config['processors']) ? $config['processors'] : [];
        static::$logger = new Logger($name, $handlers, $processors);
    }

    /**
     * @param Exception $e
     */
    public function logException(Exception $e)
    {
        $this->getLogger()->error($e->getMessage(), $e->getTrace());
    }

    /**
     * @param Error $e
     */
    public function logError(Error $e)
    {
        $this->getLogger()->error($e->getMessage(), $e->getTrace());
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level = Logger::DEBUG, $message = '', array $context = [])
    {
        $this->getLogger()->log($level, $message, $context);
    }

}
