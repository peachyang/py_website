<?php

namespace Seahinet\Lib\Session;

/**
 * Generate/Validate csrf key
 */
class Csrf
{

    public function __construct(Segment $segment = null)
    {
        if (is_null($segment)) {
            $segment = new Segment('core');
        }
        $this->segment = $segment;
        if (!$this->segment->get('csrf')) {
            $this->regenerateValue();
        }
    }

    /**
     * @param string $value
     * @return bool
     */
    public function isValid($value)
    {
        return $value === $this->getValue();
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->segment->get('csrf');
    }

    public function regenerateValue()
    {
        $hash = hash('sha512', random_bytes(32));
        $this->segment->set('csrf', $hash);
    }

}
