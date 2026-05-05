<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\VoteController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('profile', [AuthController::class, 'profile']);
});

// Posts Routes
Route::post('posts', [PostController::class, 'store']);
Route::get('posts', [PostController::class, 'index']);
Route::get('posts/{slug}', [PostController::class, 'show']);
Route::put('posts/{slug}', [PostController::class, 'update']);
Route::delete('posts/{slug}', [PostController::class, 'destroy']);

// Votes Routes
Route::post('posts/{postId}/vote', [VoteController::class, 'vote']);
Route::post('posts/{postId}/upvote', [VoteController::class, 'upvote']);
Route::post('posts/{postId}/downvote', [VoteController::class, 'downvote']);