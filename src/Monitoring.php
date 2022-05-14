<?php

namespace SoftHouse\MonitoringService;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use SoftHouse\MonitoringService\Entry\IncomingEntryCommand;
use SoftHouse\MonitoringService\Entry\IncomingEntryEvent;
use SoftHouse\MonitoringService\Entry\IncomingEntryException;
use SoftHouse\MonitoringService\Entry\IncomingEntryGates;
use SoftHouse\MonitoringService\Entry\IncomingEntryLoggly;
use SoftHouse\MonitoringService\Entry\IncomingEntryQueue;
use SoftHouse\MonitoringService\Entry\IncomingEntryRequest;
use SoftHouse\MonitoringService\Entry\IncomingEntrySchedule;
use SoftHouse\MonitoringService\Http\Resources\MonitoringResource;
use SoftHouse\MonitoringService\Models\Monitoring as MonitoringModel;
use SoftHouse\MonitoringService\Watchers\CommandWatcher;
use SoftHouse\MonitoringService\Watchers\EventsWatcher;
use SoftHouse\MonitoringService\Watchers\ExceptionWatcher;
use SoftHouse\MonitoringService\Watchers\GatesWatcher;
use SoftHouse\MonitoringService\Watchers\QueueWatcher;
use SoftHouse\MonitoringService\Watchers\RequestWatcher;
use SoftHouse\MonitoringService\Watchers\ScheduleWatcher;

class Monitoring
{
    public static $isCommandRecord = false;

    public static $isEventRecord = false;

    public static $isExceptionRecord = false;

    public static $isGatesRecord = false;

    public static $isQueueRecord = false;

    public static $isRequestRecord = false;

    public static $isScheduleRecord = false;

    public static $ignoreFrameworkEvents = true;

    public static $shouldIgnoreCommands = ['schedule:run', 'schedule:finish', 'package:discover', 'vendor:publish'];

    public static $eventsFramework = ['Illuminate\*', 'Laravel\Octane\*', 'eloquent*', 'bootstrapped*', 'bootstrapping*', 'creating*', 'composing*'];

    public static $shouldIgnoreGates = [];

    public static $shouldIgnoreRequest = ['/telescope*', '/websockets*'];

    public static $shouldIgnoreSchedule = ['schedule:run', 'schedule:finish', 'vendor:publish'];

    public static $hiddenRequestHeaders = [
        'authorization',
        'php-auth-pw',
    ];

    public static $hiddenRequestParameters = [
        'password',
        'password_confirmation',
    ];

    public static $hiddenResponseParameters = [];

    public static function isRecord()
    {
        $config = config("monitoring.watchers", []);

        self::$isCommandRecord = $config[CommandWatcher::class];
        self::$isEventRecord = $config[EventsWatcher::class];
        self::$isExceptionRecord = $config[ExceptionWatcher::class];
        self::$isGatesRecord = $config[GatesWatcher::class];
        self::$isQueueRecord = $config[QueueWatcher::class];
        self::$isRequestRecord = $config[RequestWatcher::class];
        self::$isScheduleRecord = $config[ScheduleWatcher::class];
    }

    public static function shouldIgnore(string $name, array $list): bool
    {
        return in_array($name, $list);
    }

    public static function shouldIgnoreRequest(string $event): bool
    {
        return Str::is(self::$shouldIgnoreRequest, $event);

    }

    public static function recordCommand(IncomingEntryCommand $entry)
    {
        $entry->type = EntryType::COMMAND;
        static::record(collect($entry)->toArray());
    }

    public static function recordEvent(IncomingEntryEvent $entry)
    {
        $entry->type = EntryType::EVENT;
        static::record(collect($entry)->toArray());
    }

    public static function recordException(IncomingEntryException $entry)
    {
        $entry->type = EntryType::EXCEPTION;
        static::record(collect($entry)->toArray());
    }

    public static function recordGates(IncomingEntryGates $entry)
    {
        $entry->type = EntryType::GATE;
        static::record(collect($entry)->toArray());
    }

    public static function recordQueue(IncomingEntryQueue $entry)
    {
        $entry->type = EntryType::QUEUE;
        static::record(collect($entry)->toArray());
    }

    public static function updateQueue(array $entry)
    {
        MonitoringModel::where('uuid', $entry['uuid'])->update($entry);
    }

    public static function recordRequest(IncomingEntryRequest $entry)
    {
        $entry->type = EntryType::REQUEST;
        static::record(collect($entry)->toArray());
    }

    public static function recordSchedule(IncomingEntrySchedule $entry)
    {
        $entry->type = EntryType::SCHEDULE;
        static::record(collect($entry)->toArray());
    }

    public static function recordLoggly(IncomingEntryLoggly $entry)
    {
        $entry->type = EntryType::LOGGLY;
        static::record(collect($entry)->toArray());
    }

    protected static function record($entry)
    {
        MonitoringModel::create($entry);
    }

    public static function get(string $type, $id = null)
    {
        if (is_null($id)) {
            return MonitoringModel::where('type', $type)->orderBy('created_at', 'DESC')->get();
        }
        return MonitoringModel::where('type', $type)->where('id', $id)->orderBy('created_at', 'DESC')->first();

        //SELECT id, GROUP_CONCAT(JSON_OBJECT('id', id, 'uuid', uuid, 'type', type, 'context', context)) FROM monitoring where id = '2232e72c-b94d-493c-9438-3e0bafcb322c' GROUP BY batch_uuid;
    }

    public static function resource(Collection $collection)
    {
        if ($collection->count() === 0) {
            return [];
        }
        return MonitoringResource::collection($collection);
    }

    public static function resourceBatch($collection)
    {

        if(is_null($collection)){
            return [];
        }

        $commands = MonitoringModel::select('context')->where('type', EntryType::COMMAND)->where('batch_uuid', $collection->batch_uuid)->orderBy('created_at', 'ASC')->get();
        $events = MonitoringModel::select('context')->where('type', EntryType::EVENT)->where('batch_uuid', $collection->batch_uuid)->orderBy('created_at', 'ASC')->get();
        $exceptions = MonitoringModel::select('context')->where('type', EntryType::EXCEPTION)->where('batch_uuid', $collection->batch_uuid)->orderBy('created_at', 'ASC')->get();
        $gates = MonitoringModel::select('context')->where('type', EntryType::GATE)->where('batch_uuid', $collection->batch_uuid)->orderBy('created_at', 'ASC')->get();
        $queues = MonitoringModel::select('context')->where('type', EntryType::QUEUE)->where('batch_uuid', $collection->batch_uuid)->orderBy('created_at', 'ASC')->get();
        $requests = MonitoringModel::select('context')->where('type', EntryType::REQUEST)->where('batch_uuid', $collection->batch_uuid)->orderBy('created_at', 'ASC')->get();
        $schedules = MonitoringModel::select('context')->where('type', EntryType::SCHEDULE)->where('batch_uuid', $collection->batch_uuid)->orderBy('created_at', 'ASC')->get();
        $loggly = MonitoringModel::select('context')->where('type', EntryType::LOGGLY)->where('batch_uuid', $collection->batch_uuid)->orderBy('created_at', 'ASC')->get();

        return collect($collection)->put("batch", [
           "commands" => $commands,
            "events" => $events,
            "exceptions" => $exceptions,
            "gates" => $gates,
            "queues" => $queues,
            "requests" => $requests,
            "schedules" => $schedules,
            "loggly" => $loggly
        ]);

    }


    public static function givenModel($model): string
    {
        return get_class($model) . ':' . implode('_', Arr::wrap($model->getKey()));
    }

    /**
     * @throws \ReflectionException
     */
    public static function from($target): array
    {
        return collect((new ReflectionClass($target))->getProperties())
            ->mapWithKeys(function ($property) use ($target) {
                $property->setAccessible(true);

                if (PHP_VERSION_ID >= 70400 && !$property->isInitialized($target)) {
                    return [];
                }

                if (($value = $property->getValue($target)) instanceof Model) {
                    return [$property->getName() => Monitoring::givenModel($value)];
                } elseif (is_object($value)) {
                    return [
                        $property->getName() => [
                            'class' => get_class($value),
                            'properties' => json_decode(json_encode($value), true),
                        ],
                    ];
                } else {
                    return [$property->getName() => json_decode(json_encode($value), true)];
                }
            })->toArray();
    }

    public static function storageQueueStopping(): string
    {

        return storage_path('framework/stoppingQueue.php');
    }
}
