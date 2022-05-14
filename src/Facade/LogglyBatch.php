<?php

namespace SoftHouse\MonitoringService\Facade;
use Illuminate\Support\Facades\Facade;
use SoftHouse\MonitoringService\Loggly\LogglyBatch as LogglyFacade;

class LogglyBatch extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LogglyFacade::class;
    }

}
