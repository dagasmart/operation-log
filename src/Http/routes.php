<?php

use Illuminate\Support\Facades\Route;
use DagaSmart\OperationLog\Http\Controllers;

Route::resource('admin_operation_log', Controllers\OperationLogController::class)->only([
    'index',
    'show',
    'destroy'
]);

//清理当前相关数据
Route::post('admin_operation_log/clean', [Controllers\OperationLogController::class, 'clean']);
//清空所有数据并重建索引
Route::post('admin_operation_log/truncate', [Controllers\OperationLogController::class, 'truncate']);
