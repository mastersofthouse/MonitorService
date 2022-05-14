<?php

use Illuminate\Support\Facades\Route;
use SoftHouse\MonitoringService\Http\Controllers\MonitoringController;

Route::get('/monitoring/commands', [MonitoringController::class, 'loggly'])->name('monitoring.commands');
Route::get('/monitoring/commands/{id}', [MonitoringController::class, 'loggly'])->name('monitoring.commands.batch');

Route::get('/monitoring/events', [MonitoringController::class, 'events'])->name('monitoring.events');
Route::get('/monitoring/events/{id}', [MonitoringController::class, 'events'])->name('monitoring.events.batch');

Route::get('/monitoring/exceptions', [MonitoringController::class, 'exception'])->name('monitoring.exceptions');
Route::get('/monitoring/exceptions/{id}', [MonitoringController::class, 'exception'])->name('monitoring.exceptions.batch');

Route::get('/monitoring/gates', [MonitoringController::class, 'gate'])->name('monitoring.gates');
Route::get('/monitoring/gates/{id}', [MonitoringController::class, 'gate'])->name('monitoring.gates.batch');

Route::get('/monitoring/queues', [MonitoringController::class, 'queue'])->name('monitoring.queues');
Route::get('/monitoring/queues/{id}', [MonitoringController::class, 'queue'])->name('monitoring.queues.batch');

Route::get('/monitoring/requests', [MonitoringController::class, 'request'])->name('monitoring.requests');
Route::get('/monitoring/requests/{id}', [MonitoringController::class, 'request'])->name('monitoring.requests.batch');

Route::get('/monitoring/schedules', [MonitoringController::class, 'schedule'])->name('monitoring.schedule');
Route::get('/monitoring/schedules/{id}', [MonitoringController::class, 'schedule'])->name('monitoring.schedule.batch');

Route::get('/monitoring/loggly', [MonitoringController::class, 'loggly'])->name('monitoring.loggly');
Route::get('/monitoring/loggly/{id}', [MonitoringController::class, 'loggly'])->name('monitoring.loggly.batch');
