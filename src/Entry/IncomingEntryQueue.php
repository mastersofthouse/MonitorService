<?php

namespace SoftHouse\MonitoringService\Entry;

class IncomingEntryQueue extends IncomingEntry
{
    public $uuid;
    public $context = null;

    public function __construct(array $content, $uuid = null)
    {
        $this->uuid = $content["uuid"];
        $this->context = $content;

        $this->batchUuid();
    }
}
