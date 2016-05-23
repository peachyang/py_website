<?php

namespace Seahinet\Lib\ViewModel;

use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Stdlib\Singleton;

class Message extends AbstractViewModel implements Singleton
{

    protected static $instance = null;
    protected $segments = [];

    private function __construct()
    {
        $this->setTemplate('page/message');
    }

    public static function instance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * Add session segment to handle
     * 
     * @param string $name
     * @return Message
     */
    public function addHandledSegment($name)
    {
        if (is_array($name)) {
            $this->segments += $name;
        } else {
            $this->segments[] = $name;
        }
        return $this;
    }

    /**
     * Get messages from session
     * 
     * @return array
     */
    public function getMessages()
    {
        $messages = [];
        foreach ($this->segments as $name) {
            $segment = new Segment($name);
            $messages += $segment->get('message', []);
            $segment->set('message', []);
        }
        return $messages;
    }

}
