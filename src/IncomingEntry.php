<?php

namespace SoftHouse\MonitoringService;

use Illuminate\Support\Str;

class IncomingEntry
{
    public $uuid;

    public $batchId;

    public $type;

    public $familyHash;

    public $user;

    public $content = [];

    public $tags = [];

    public $recordedAt;

    public function __construct(array $content, $uuid = null)
    {
        $this->uuid = $uuid ?: (string) Str::orderedUuid();

        $this->recordedAt = now();

        $this->content = array_merge($content, ['hostname' => gethostname()]);
    }

    public static function make(...$arguments)
    {
        return new static(...$arguments);
    }

    public function batchId(string $batchId)
    {
        $this->batchId = $batchId;

        return $this;
    }


    public function type(string $type)
    {
        $this->type = $type;

        return $this;
    }


    public function withFamilyHash($familyHash)
    {
        $this->familyHash = $familyHash;

        return $this;
    }


    public function user($user)
    {
        $this->user = $user;

        $this->content = array_merge($this->content, [
            'user' => [
                'id' => $user->getAuthIdentifier(),
                'name' => $user->name ?? null,
                'email' => $user->email ?? null,
            ],
        ]);

        $this->tags(['Auth:'.$user->getAuthIdentifier()]);

        return $this;
    }


    public function tags(array $tags)
    {
        $this->tags = array_unique(array_merge($this->tags, $tags));

        return $this;
    }


//    public function hasMonitoredTag()
//    {
//        if (! empty($this->tags)) {
//            return app(EntriesRepository::class)->isMonitoring($this->tags);
//        }
//
//        return false;
//    }
//
//
    public function isRequest()
    {
        return $this->type === EntryType::REQUEST;
    }

//
    public function isFailedRequest()
    {
        return $this->type === EntryType::REQUEST &&
            ($this->content['response_status'] ?? 200) >= 500;
    }

    public function isQuery()
    {
        return $this->type === EntryType::QUERY;
    }
//
//
//    public function isSlowQuery()
//    {
//        return $this->type === EntryType::QUERY && ($this->content['slow'] ?? false);
//    }
//
//
//    public function isGate()
//    {
//        return $this->type === EntryType::GATE;
//    }
//
//
//    public function isFailedJob()
//    {
//        return $this->type === EntryType::JOB &&
//            ($this->content['status'] ?? null) === 'failed';
//    }
//
//
//    public function isReportableException()
//    {
//        return false;
//    }
//
//
//    public function isException()
//    {
//        return false;
//    }
//
//
//    public function isDump()
//    {
//        return false;
//    }
//
//
//    public function isScheduledTask()
//    {
//        return $this->type === EntryType::SCHEDULED_TASK;
//    }
//
//
//    public function isClientRequest()
//    {
//        return $this->type === EntryType::CLIENT_REQUEST;
//    }


    public function familyHash()
    {
        return $this->familyHash;
    }


    public function toArray()
    {
        return [
            'uuid' => $this->uuid,
            'batch_id' => $this->batchId,
            'family_hash' => $this->familyHash,
            'type' => $this->type,
            'content' => $this->content,
            'created_at' => $this->recordedAt->toDateTimeString(),
        ];
    }
}
