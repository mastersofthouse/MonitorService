<?php

namespace SoftHouse\MonitoringService;


use Illuminate\Auth\Access\Events\GateEvaluated;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\WorkerStopping;
use Illuminate\Queue\Queue;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use SoftHouse\MonitoringService\Http\Middleware\MonitoringMiddleware;
use SoftHouse\MonitoringService\Loggly\LogglyBatch;
use SoftHouse\MonitoringService\Watchers\CommandWatcher;
use SoftHouse\MonitoringService\Watchers\EventsWatcher;
use SoftHouse\MonitoringService\Watchers\ExceptionWatcher;
use SoftHouse\MonitoringService\Watchers\GatesWatcher;
use SoftHouse\MonitoringService\Watchers\QueueWatcher;
use SoftHouse\MonitoringService\Watchers\RequestWatcher;
use SoftHouse\MonitoringService\Watchers\ScheduleWatcher;

class MonitoringServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPublishing();

        Monitoring::isRecord();

        $this->app['router']->pushMiddlewareToGroup('web', MonitoringMiddleware::class);
        $this->app['router']->pushMiddlewareToGroup('api', MonitoringMiddleware::class);

        Queue::createPayloadUsing(function ($connection, $queue, $payload) {
            return ["uuid" => optional((new Watchers\QueueWatcher)->recordJob($connection, $queue, $payload))->uuid];
        });
    }

    public function register()
    {

        $this->app->scoped(LogglyBatch::class);

        $this->app['events']->listen(CommandFinished::class, [CommandWatcher::class, 'recordCommand']);
        $this->app['events']->listen('*', [EventsWatcher::class, 'recordEvent']);
        $this->app['events']->listen(MessageLogged::class, [ExceptionWatcher::class, 'recordException']);
        $this->app['events']->listen(GateEvaluated::class, [GatesWatcher::class, 'handleGateEvaluated']);

        if (Monitoring::$isQueueRecord) {
            $this->app['events']->listen(WorkerStopping::class, [QueueWatcher::class, 'recordProcessedStopping']);
            $this->app['events']->listen(JobProcessed::class, [QueueWatcher::class, 'recordProcessedJob']);
            $this->app['events']->listen(JobFailed::class, [QueueWatcher::class, 'recordFailedJob']);
        }

        $this->app['events']->listen(RequestHandled::class, [RequestWatcher::class, 'recordRequest']);
        $this->app['events']->listen(CommandStarting::class, [ScheduleWatcher::class, 'recordCommand']);
    }

    private function registerPublishing()
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/../config/monitoring.php' => config_path('monitoring.php'),
            ], 'monitoring-config');
        }


        $this->mergeConfigFrom(
            __DIR__ . '/../config/monitoring.php', 'monitoring'
        );

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->registerRoutes();
    }

    protected function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    protected function routeConfiguration()
    {
        return [
            'prefix' => config('monitoring.router.prefix'),
            'middleware' => config('monitoring.router.middleware'),
        ];
    }


}
