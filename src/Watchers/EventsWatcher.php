<?php

namespace SoftHouse\MonitoringService\Watchers;

use Closure;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use ReflectionException;
use ReflectionFunction;
use SoftHouse\MonitoringService\Entry\IncomingEntryEvent;
use SoftHouse\MonitoringService\Monitoring;

class EventsWatcher
{
    /**
     * @throws ReflectionException
     */
    public function recordEvent($eventName, $payload)
    {
        if(!Monitoring::$isEventRecord){
            return;
        }

        if( Str::is(Monitoring::$eventsFramework, $eventName) && Monitoring::$ignoreFrameworkEvents){
            return;
        }

        $formattedPayload = $this->extractPayload($eventName, $payload);

        Monitoring::recordEvent(IncomingEntryEvent::make([
            'name' => $eventName,
            'payload' => empty($formattedPayload) ? null : $formattedPayload,
            'listeners' => $this->formatListeners($eventName),
            'broadcast' => class_exists($eventName) && in_array(ShouldBroadcast::class, (array)class_implements($eventName)),
        ]));
    }

    /**
     * @throws ReflectionException
     */
    protected function extractPayload($eventName, $payload): array
    {
        if (class_exists($eventName) && isset($payload[0]) && is_object($payload[0])) {
            return Monitoring::from($payload[0]);
        }

        return collect($payload)->map(function ($value) {
            return is_object($value) ? [
                'class' => get_class($value),
                'properties' => json_decode(json_encode($value), true),
            ] : $value;
        })->toArray();
    }

    protected function formatListeners($eventName): array
    {
        return collect(app('events')->getListeners($eventName))
            ->map(function ($listener) {
                $listener = (new ReflectionFunction($listener))
                    ->getStaticVariables()['listener'];

                if (is_string($listener)) {
                    return Str::contains($listener, '@') ? $listener : $listener.'@handle';
                } elseif (is_array($listener) && is_string($listener[0])) {
                    return $listener[0].'@'.$listener[1];
                } elseif (is_array($listener) && is_object($listener[0])) {
                    return get_class($listener[0]).'@'.$listener[1];
                }

                return $this->formatClosureListener($listener);
            })->reject(function ($listener) {
                return Str::contains($listener, ['Laravel\\Telescope', 'SoftHouse\\MonitoringService']);
            })->map(function ($listener) {
                if (Str::contains($listener, '@')) {
                    $queued = in_array(ShouldQueue::class, class_implements(explode('@', $listener)[0]));
                }

                return [
                    'name' => $listener,
                    'queued' => $queued ?? false,
                ];
            })->values()->toArray();
    }


    /**
     * @throws ReflectionException
     */
    protected function formatClosureListener(Closure $listener): string
    {
        $listener = new ReflectionFunction($listener);

        return sprintf('Closure at %s[%s:%s]',
            $listener->getFileName(),
            $listener->getStartLine(),
            $listener->getEndLine()
        );
    }
}
