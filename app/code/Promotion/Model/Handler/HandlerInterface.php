<?php

namespace Seahinet\Promotion\Model\Handler;

interface HandlerInterface
{

    /**
     * @param array $items
     * @param \Seahinet\Promotion\Model\Handler $handler
     * @return array
     */
    public function matchItems($items, $handler);
}
