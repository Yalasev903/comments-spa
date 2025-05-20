<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CommentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/comments', [CommentController::class, 'index']);
Route::post('/comments', [CommentController::class, 'store']);
Route::get('/comments/{comment}/download', [CommentController::class, 'downloadAttachment']);