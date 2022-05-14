<?php

namespace SoftHouse\MonitoringService;

class EntryType
{
    public const COMMAND = 'command';
    public const EVENT = 'event';
    public const EXCEPTION = 'exception';
    public const GATE = 'gate';
    public const QUEUE = 'queue';
    public const REQUEST = 'request';
    public const SCHEDULE = 'schedule';
    public const LOGGLY = 'loggly';
    public const QUERY = 'query';
}
