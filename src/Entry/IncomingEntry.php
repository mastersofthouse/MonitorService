<?php

namespace SoftHouse\MonitoringService\Entry;

use SoftHouse\MonitoringService\Facade\LogglyBatch;

abstract class IncomingEntry
{
    public $batch_uuid;

    public $authentication;

    public $tenant;

    public $hostname;

    private function authentication(): IncomingEntry
    {

        if (!is_null(config('monitoring.authentication_resolve'))) {
            $authResolve = config('monitoring.authentication_resolve');

            if (method_exists($authResolve, "get")) {
                $resolve = $authResolve::get();

                if (is_array($resolve)) {

                    $this->authentication = [
                        "id" => array_key_exists("id", $resolve) ? $resolve["id"] : null,
                        "name" => array_key_exists("name", $resolve) ? $resolve["name"] : null,
                        "email" => array_key_exists("email", $resolve) ? $resolve["email"] : null,
                    ];

                    return $this;
                }
            }
        }

        if (auth()->check()) {

            $this->authentication = [
                "id" => auth()->id(),
                "name" => auth()->user()->name,
                "email" => auth()->user()->email,
            ];
        }

        return $this;
    }

    private function tenant(): IncomingEntry
    {
        if (!is_null(config('monitoring.tenant_resolve'))) {
            $tenantResolve = config('monitoring.tenant_resolve');

            if (method_exists($tenantResolve, "get")) {
                $resolve = $tenantResolve::get();

                if (is_array($resolve)) {

                    $this->tenant = [
                        "id" => array_key_exists("id", $resolve) ? $resolve["id"] : null,
                        "name" => array_key_exists("name", $resolve) ? $resolve["name"] : null,
                    ];

                    return $this;
                }
            }
        }

        if (class_exists(\Stancl\Tenancy\Tenancy::class)) {

            if (tenancy()->initialized) {
                $this->tenant = [
                    'id' => \tenancy()->tenant->getKey(),
                    'name' => \tenancy()->tenant->getName()
                ];
                return $this;
            } else {

                $this->tenant = [
                    'id' => 'Central',
                    'name' => 'Central'
                ];
                return $this;
            }
        }

        $this->tenant = [
            'id' => 'Central',
            'name' => 'Central'
        ];
        return $this;
    }

    public function batchUuid(): IncomingEntry
    {
        $this->batch_uuid = LogglyBatch::getUuid();
        $this->hostname = gethostname();
        $this->authentication();
        $this->tenant();
        return $this;
    }

    public static function make(...$arguments)
    {
        return new static(...$arguments);
    }
}
