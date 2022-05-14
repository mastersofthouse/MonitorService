<?php

namespace SoftHouse\MonitoringService\Watchers;

use Illuminate\Console\Events\CommandFinished;
use SoftHouse\MonitoringService\Entry\IncomingEntryCommand;
use SoftHouse\MonitoringService\Monitoring;

class CommandWatcher
{
    public function recordCommand(CommandFinished $event)
    {
        if(!Monitoring::$isCommandRecord){
            return;
        }

        if (Monitoring::shouldIgnore($event->command ?? $event->input->getArguments()['command'] ?? 'default', Monitoring::$shouldIgnoreCommands)) {
            return;
        }

        Monitoring::recordCommand(IncomingEntryCommand::make([
            'command' => $event->command ?? $event->input->getArguments()['command'] ?? 'default',
            'exit_code' => $event->exitCode,
            'arguments' => $event->input->getArguments(),
            'options' => $event->input->getOptions(),
        ]));
    }
}
