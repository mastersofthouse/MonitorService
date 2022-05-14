<?php

namespace SoftHouse\MonitoringService\Entry;

use Illuminate\Support\Str;
use Stancl\Tenancy\Tenancy;
use Throwable;

class IncomingEntryException extends IncomingEntry
{
    public $context = null;

    public function __construct(Throwable $exception, array $content)
    {
        $this->context = $content;

        $this->batchUuid();
    }
}
