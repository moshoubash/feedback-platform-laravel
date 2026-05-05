<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\VoteController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Posts Routes
Route::get('posts', [PostController::class, 'index']);
Route::get('posts/{slug}', [PostController::class, 'show']);
Route::get('posts/{slug}/votes', [PostController::class, 'votes']);

Route::middleware('auth:sanctum')->group(function () {
    // Auth Routes
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('profile', [AuthController::class, 'profile']);

    // Post Routes
    Route::post('posts', [PostController::class, 'store']);
    Route::put('posts/{slug}', [PostController::class, 'update']);
    Route::delete('posts/{slug}', [PostController::class, 'destroy']);

    // Vote Routes
    Route::post('posts/{postId}/vote', [VoteController::class, 'vote']);
    Route::patch('posts/{postId}/upvote', [VoteController::class, 'upvote']);
    Route::patch('posts/{postId}/downvote', [VoteController::class, 'downvote']);
});