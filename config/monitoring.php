<?php

return [

    "watchers" => [
        \SoftHouse\MonitoringService\Watchers\CommandWatcher::class => true,
        \SoftHouse\MonitoringService\Watchers\EventsWatcher::class => true,
        \SoftHouse\MonitoringService\Watchers\ExceptionWatcher::class => true,
        \SoftHouse\MonitoringService\Watchers\GatesWatcher::class => true,
        \SoftHouse\MonitoringService\Watchers\QueueWatcher::class => true,
        \SoftHouse\MonitoringService\Watchers\RequestWatcher::class => true,
        \SoftHouse\MonitoringService\Watchers\ScheduleWatcher::class => true

    ],

    "router" => [
        'prefix' => null,
        'middleware' => "auth:sanctum",
    ],

    "authentication_resolve" => \App\Http\AuthResolve::class,
    "tenant_resolve" => null,
];
