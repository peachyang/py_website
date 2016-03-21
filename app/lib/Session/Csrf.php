<?php

namespace Seahinet\Lib\Session;

class Csrf
{

    public function __construct(Segment $segment = null)
    {
        if (is_null($segment)) {
            $segment = new Segment('core');
        }
        $this->segment = $segment;
        if (!$this->segment->get('value')) {
            $this->regenerateValue();
        }
    }

    public function isValid($value)
    {
        return $value === $this->getValue();
    }

    public function getValue()
    {
        return $this->segment->get('value');
    }

    public function regenerateValue()
    {
        $hash = hash('sha512', random_bytes(32));
        $this->segment->set('value', $hash);
    }

}
