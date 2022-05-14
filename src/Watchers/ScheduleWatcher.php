<?php

namespace SoftHouse\MonitoringService\Watchers;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use SoftHouse\MonitoringService\Entry\IncomingEntrySchedule;
use SoftHouse\MonitoringService\Monitoring;

class ScheduleWatcher
{
    public function recordCommand(CommandStarting $event)
    {
        if (!Monitoring::$isScheduleRecord) {
            return;
        }

        if (Monitoring::shouldIgnore(is_null($event->command) ? "" : $event->command, Monitoring::$shouldIgnoreSchedule)) {
            return;
        }

        $command = $event instanceof CallbackEvent ? 'Closure' : $event->command;

        if(File::exists(Monitoring::storageQueueStopping()) && Str::is(['queue:work'], $command)){
            File::delete(Monitoring::storageQueueStopping());
        }

        collect(app(Schedule::class)->events())->each(function ($event) {
            $event->then(function () use ($event) {
                Monitoring::recordSchedule(IncomingEntrySchedule::make([
                    'command' => $event instanceof CallbackEvent ? 'Closure' : $event->command,
                    'description' => $event->description,
                    'expression' => $event->expression,
                    'timezone' => $event->timezone,
                    'user' => $event->user,
                    'output' => $this->getEventOutput($event),
                ]));
            });
        });
    }

    protected function getEventOutput(Event $event): string
    {
        if (! $event->output ||
            $event->output === $event->getDefaultOutput() ||
            $event->shouldAppendOutput ||
            ! file_exists($event->output)) {
            return '';
        }

        return trim(file_get_contents($event->output));
    }
}
