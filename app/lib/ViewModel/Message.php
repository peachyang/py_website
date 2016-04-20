<?php

namespace Seahinet\Lib\ViewModel;

use Seahinet\Lib\Session\Segment;
use Seahinet\Lib\Stdlib\Singleton;

class Message extends AbstractViewModel implements Singleton
{

    protected static $viewModel = null;
    protected $segments = [];

    private function __construct()
    {
        $this->setTemplate('page/message');
    }

    public static function instance()
    {
        if (is_null(static::$viewModel)) {
            static::$viewModel = new static;
        }
        return static::$viewModel;
    }

    public function addHandledSegment($name)
    {
        if (is_array($name)) {
            $this->segments+=$name;
        } else {
            $this->segments[] = $name;
        }
        return $this;
    }

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
