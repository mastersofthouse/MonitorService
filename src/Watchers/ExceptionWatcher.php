<?php

namespace SoftHouse\MonitoringService\Watchers;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Arr;
use SoftHouse\MonitoringService\Context\ExceptionContext;
use SoftHouse\MonitoringService\Entry\IncomingEntryException;
use SoftHouse\MonitoringService\Monitoring;
use Throwable;

class ExceptionWatcher
{

    public function recordException(MessageLogged $event)
    {
        if (!Monitoring::$isExceptionRecord) {
            return;
        }

        if ($this->shouldIgnore($event)) {
            return;
        }

        $exception = $event->context['exception'];

        $trace = collect($exception->getTrace())->map(function ($item) {
            return Arr::only($item, ['file', 'line']);
        })->toArray();


        $entry = IncomingEntryException::make($exception, [
            'class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'message' => $exception->getMessage(),
            'context' => transform(Arr::except($event->context, ['exception']), function ($context) {
                return !empty($context) ? $context : null;
            }),
            'trace' => $trace,
            'line_preview' => ExceptionContext::get($exception),
        ]);

        Monitoring::recordException($entry);
    }

    /**
     * Determine if the event should be ignored.
     *
     * @param mixed $event
     * @return bool
     */
    private function shouldIgnore($event): bool
    {
        return !isset($event->context['exception']) ||
            !$event->context['exception'] instanceof Throwable;
    }
}
