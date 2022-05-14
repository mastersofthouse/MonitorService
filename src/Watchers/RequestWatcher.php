<?php

namespace SoftHouse\MonitoringService\Watchers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SoftHouse\MonitoringService\Entry\IncomingEntryRequest;
use SoftHouse\MonitoringService\Monitoring;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Response as IlluminateResponse;

class RequestWatcher
{
    public function recordRequest(RequestHandled $event)
    {
        if (!Monitoring::$isRequestRecord) {
            return;
        }

        if ($this->shouldIgnoreHttpMethod($event) ||
            $this->shouldIgnoreStatusCode($event) ||
        Monitoring::shouldIgnoreRequest(str_replace($event->request->root(), '', $event->request->fullUrl()) ?: '/')) {
            return;
        }

        $startTime = defined('LARAVEL_START') ? LARAVEL_START : $event->request->server('REQUEST_TIME_FLOAT');

        Monitoring::recordRequest(IncomingEntryRequest::make([
            'ip_address' => $event->request->ip(),
            'uri' => str_replace($event->request->root(), '', $event->request->fullUrl()) ?: '/',
            'method' => $event->request->method(),
            'controller_action' => optional($event->request->route())->getActionName(),
            'middleware' => array_values(optional($event->request->route())->gatherMiddleware() ?? []),
            'headers' => $this->headers($event->request->headers->all()),
            'payload' => $this->payload($this->input($event->request)),
            'session' => $this->payload($this->sessionVariables($event->request)),
            'response_status' => $event->response->getStatusCode(),
            'response' => $this->response($event->response),
            'duration' => $startTime ? floor((microtime(true) - $startTime) * 1000) : null,
            'memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 1),
        ]));
    }

    protected function headers($headers)
    {
        $headers = collect($headers)->map(function ($header) {
            return $header[0];
        })->toArray();

        return $this->hideParameters($headers,
            Monitoring::$hiddenRequestHeaders
        );
    }

    protected function payload($payload)
    {
        return $this->hideParameters($payload,
            Monitoring::$hiddenRequestParameters
        );
    }

    private function input(Request $request): array
    {
        $files = $request->files->all();

        array_walk_recursive($files, function (&$file) {
            $file = [
                'name' => $file->getClientOriginalName(),
                'size' => $file->isFile() ? ($file->getSize() / 1000).'KB' : '0',
            ];
        });

        return array_replace_recursive($request->input(), $files);
    }

    private function sessionVariables(Request $request): array
    {
        return $request->hasSession() ? $request->session()->all() : [];
    }


    protected function response(Response $response)
    {
        $content = $response->getContent();

        if (is_string($content)) {
            if (is_array(json_decode($content, true)) &&
                json_last_error() === JSON_ERROR_NONE) {
                return $this->contentWithinLimits($content)
                    ? $this->hideParameters(json_decode($content, true), Monitoring::$hiddenResponseParameters)
                    : 'Purged By Monitoring';
            }

            if (Str::startsWith(strtolower($response->headers->get('Content-Type') ?? ''), 'text/plain')) {
                return $this->contentWithinLimits($content) ? $content : 'Purged By Monitoring';
            }
        }

        if ($response instanceof RedirectResponse) {
            return 'Redirected to '.$response->getTargetUrl();
        }

        if ($response instanceof IlluminateResponse && $response->getOriginalContent() instanceof View) {
            return [
                'view' => $response->getOriginalContent()->getPath(),
                'data' => $this->extractDataFromView($response->getOriginalContent()),
            ];
        }

        return 'HTML Response';
    }

    protected function hideParameters($data, $hidden)
    {
        foreach ($hidden as $parameter) {
            if (Arr::get($data, $parameter)) {
                Arr::set($data, $parameter, '********');
            }
        }

        return $data;
    }

    public function contentWithinLimits($content): bool
    {
        $limit = $this->options['size_limit'] ?? 64;

        return intdiv(mb_strlen($content), 1000) <= $limit;
    }

    protected function extractDataFromView($view): array
    {
        return collect($view->getData())->map(function ($value) {
            if ($value instanceof Model) {
                return Monitoring::givenModel($value);
            } elseif (is_object($value)) {
                return [
                    'class' => get_class($value),
                    'properties' => json_decode(json_encode($value), true),
                ];
            } else {
                return json_decode(json_encode($value), true);
            }
        })->toArray();
    }

    protected function shouldIgnoreHttpMethod($event): bool
    {
        return in_array(
            strtolower($event->request->method()),
            collect($this->options['ignore_http_methods'] ?? [])->map(function ($method) {
                return strtolower($method);
            })->all()
        );
    }

    protected function shouldIgnoreStatusCode($event): bool
    {
        return in_array(
            $event->response->getStatusCode(),
            $this->options['ignore_status_codes'] ?? []
        );
    }
}
