<?php

namespace SoftHouse\MonitoringService\Watchers;

use Illuminate\Auth\Access\Events\GateEvaluated;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SoftHouse\MonitoringService\Entry\IncomingEntryGates;
use SoftHouse\MonitoringService\Monitoring;

class GatesWatcher
{
    public function handleGateEvaluated(GateEvaluated $event)
    {

        if (!Monitoring::$isGatesRecord) {
            return;
        }

        if (Monitoring::shouldIgnore($event->ability, Monitoring::$shouldIgnoreGates)) {
            return;
        }

        $caller = $this->getCallerFromStackTrace([0, 1]);

        Monitoring::recordGates(IncomingEntryGates::make([
            'ability' => $event->ability,
            'result' => $this->gateResult($event->result),
            'arguments' => $this->formatArguments($event->arguments),
            'file' => $caller['file'] ?? null,
            'line' => $caller['line'] ?? null,
        ]));


        return $event->result;
    }

    private function gateResult($result): string
    {
        if ($result instanceof Response) {
            return $result->allowed() ? 'allowed' : 'denied';
        }

        return $result ? 'allowed' : 'denied';
    }

    protected function getCallerFromStackTrace($forgetLines = 0)
    {
        $trace = collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))->forget($forgetLines);

        return $trace->first(function ($frame) {
            if (!isset($frame['file'])) {
                return false;
            }

            return !Str::contains($frame['file'],
                base_path('vendor' . DIRECTORY_SEPARATOR . $this->ignoredVendorPath())
            );
        });
    }

    /**
     * Choose the frame outside of either Telescope/Laravel or all packages.
     *
     * @return string|null
     */
    protected function ignoredVendorPath(): ?string
    {
        if (!($this->options['ignore_packages'] ?? true)) {
            return 'laravel';
        }
    }

    private function formatArguments($arguments): array
    {
        return collect($arguments)->map(function ($argument) {
            return $argument instanceof Model ? Monitoring::givenModel($argument) : $argument;
        })->toArray();
    }


}
