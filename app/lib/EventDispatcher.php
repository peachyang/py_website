<?php

namespace Seahinet\Lib;

use Seahinet\Lib\EventDispatcher\Event;
use Seahinet\Lib\Stdlib\Singleton;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;

class EventDispatcher extends SymfonyEventDispatcher implements Singleton
{

    protected static $instance = null;

    /**
     * @param string $eventName
     * @param Event|array $event
     * @return Event
     */
    public function trigger($eventName, $event = [])
    {
        if (is_array($event)) {
            $event = new Event($event);
        }
        return $this->dispatch($eventName, $event);
    }

    /**
     * @param string $eventName
     * @param array|Listeners\ListenerInterface $listener
     * @param int $priority
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        if (is_array($listener) && is_subclass_of($listener[0], '\\Seahinet\\Lib\\Listeners\\ListenerInterface')) {
            $listener[0] = new $listener[0];
        }
        parent::addListener($eventName, $listener, $priority);
    }

    public static function instance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }

}
