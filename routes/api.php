<?php

use Illuminate\Support\Facades\Route;
use Amghrby\Workflows\Http\Controllers\WorkflowController;

Route::middleware('api')->prefix('api')->group(function() {
    Route::post('workflows/{id}/trigger', [WorkflowController::class, 'addTrigger']);
    Route::apiResource('workflows', WorkflowController::class);
});
