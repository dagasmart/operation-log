<?php

use Illuminate\Support\Facades\Route;
use DagaSmart\OperationLog\Http\Controllers;

Route::resource('admin_operation_log', Controllers\OperationLogController::class)->only([
    'index',
    'show',
    'destroy',
]);
