<?php


if (!function_exists('loggly')) {
    function loggly(): SoftHouse\MonitoringService\Loggly\Loggly
    {
        return app(SoftHouse\MonitoringService\Loggly\Loggly::class);
    }
}
