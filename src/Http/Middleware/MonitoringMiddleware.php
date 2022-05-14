<?php

namespace SoftHouse\MonitoringService\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SoftHouse\MonitoringService\Facade\LogglyBatch;

class MonitoringMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        LogglyBatch::startBatch();
        return $next($request);
    }
}
