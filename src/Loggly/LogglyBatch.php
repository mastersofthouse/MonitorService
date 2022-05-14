<?php

namespace SoftHouse\MonitoringService\Loggly;
use Illuminate\Support\Str;

class LogglyBatch
{
    public ?string $uuid = null;

    public int $transactions = 0;

    public array $logs = [];

    protected function generateUuid(): string
    {
        return Str::uuid()->toString();
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function startBatch(): void
    {
        if (! $this->isOpen()) {
            $this->uuid = $this->generateUuid();
        }

        $this->transactions++;
    }

    public function isOpen(): bool
    {
        return $this->transactions > 0;
    }

    public function endBatch(): void
    {
        $this->transactions = max(0, $this->transactions - 1);

        if ($this->transactions === 0) {
            $this->uuid = null;
        }
    }
}
