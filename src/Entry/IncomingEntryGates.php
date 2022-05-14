<?php

namespace SoftHouse\MonitoringService\Entry;

class IncomingEntryGates extends IncomingEntry
{
    public $context = null;

    public function __construct(array $content, $uuid = null)
    {
        $this->context = $content;

        $this->batchUuid();
    }
}
