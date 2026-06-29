<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFileController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('projects', ProjectController::class);

    Route::get('projects/{project}/files', [ProjectFileController::class, 'index']);
    Route::post('projects/{project}/files', [ProjectFileController::class, 'store']);
    Route::get('projects/{project}/files/{file}', [ProjectFileController::class, 'show']);
    Route::delete('projects/{project}/files/{file}', [ProjectFileController::class, 'destroy']);

    Route::get('projects/{project}/messages', [ChatMessageController::class, 'index']);
    Route::post('projects/{project}/messages', [ChatMessageController::class, 'store']);
});
