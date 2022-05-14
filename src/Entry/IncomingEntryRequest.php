<?php

namespace SoftHouse\MonitoringService\Entry;

class IncomingEntryRequest extends IncomingEntry
{
    public $context;

    public function __construct(array $content, $uuid = null)
    {
        $this->context = $content;
        $this->batchUuid();
    }
}
