<?php

namespace SoftHouse\MonitoringService\Watchers;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\WorkerStopping;
use Laravel\Telescope\ExceptionContext;
use Laravel\Telescope\ExtractProperties;
use SoftHouse\MonitoringService\Entry\IncomingEntryQueue;
use SoftHouse\MonitoringService\Monitoring;

class QueueWatcher
{
    public function recordJob($connection, $queue, array $payload)
    {

        $content = array_merge([
            'status' => 'pending',
        ], $this->defaultJobData($connection, $queue, $payload, $this->data($payload)));

        $entry = IncomingEntryQueue::make($content);

        Monitoring::recordQueue($entry);
        return $entry;
    }

    public function recordProcessedStopping(WorkerStopping $event)
    {

        try {
            if ($event->status === 0) {
                file_put_contents(Monitoring::storageQueueStopping(), time());
            }
        } catch (\Exception $exception) {

        }
    }

    public function recordProcessedJob(JobProcessed $event)
    {
        $uuid = $event->job->payload()['uuid'] ?? null;

        if (!$uuid) {
            return;
        }
        Monitoring::updateQueue([
            'uuid' => $uuid,
            'context->status' => 'processed'
        ]);
    }

    public function recordFailedJob(JobFailed $event)
    {
        $uuid = $event->job->payload()['telescope_uuid'] ?? null;

        if (!$uuid) {
            return;
        }

        Monitoring::updateQueue([
            'uuid' => $uuid,
            'context->status' => 'failed',
            'context->exception' => [
                'message' => $event->exception->getMessage(),
                'trace' => $event->exception->getTrace(),
                'line' => $event->exception->getLine(),
                'line_preview' => ExceptionContext::get($event->exception),
            ],

        ]);
    }

    protected function defaultJobData($connection, $queue, array $payload, array $data)
    {
        return [
            'uuid' => $payload["uuid"],
            'connection' => $connection,
            'queue' => $queue,
            'name' => $payload['displayName'],
            'tries' => $payload['maxTries'],
            'timeout' => $payload['timeout'],
            'data' => $data,
        ];
    }

    protected function data(array $payload)
    {
        if (!isset($payload['data']['command'])) {
            return $payload['data'];
        }

        return ExtractProperties::from(
            $payload['data']['command']
        );
    }
}
