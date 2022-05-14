<?php

namespace SoftHouse\MonitoringService\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonitoringResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "uuid" => $this->uuid,
            "batch_uuid" => $this->batch_uuid,
            "authentication" => $this->authentication,
            "tenant" => $this->tenant,
            "type" => $this->type,
            "hostname" => $this->hostname,
            "context" => $this->context,
        ];
    }
}
