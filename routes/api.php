<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\VoteController;
use App\Http\Controllers\Api\UserController;

Route::middleware('throttle:auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('posts/get-posts-for-authenticated-users', [PostController::class, 'getPostsForAuthenticatedUsers']);
});

Route::middleware('throttle:api')->group(function () {
    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{slug}', [PostController::class, 'show']);
    Route::get('posts/{slug}/votes', [PostController::class, 'votes']);
});

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // Auth
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('profile', [AuthController::class, 'profile']);

    // User Management
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{user}', [UserController::class, 'show']);
    Route::get('users/{user}/posts', [UserController::class, 'showWithPosts']);
    Route::put('users/{user}', [UserController::class, 'update']);
    Route::delete('users/{user}', [UserController::class, 'destroy']);

    // Posts (write)
    Route::post('posts', [PostController::class, 'store']);
    Route::put('posts/{slug}', [PostController::class, 'update']);
    Route::delete('posts/{slug}', [PostController::class, 'destroy']);

    // Votes (stricter limit: 10 req/min to prevent spam)
    Route::middleware('throttle:voting')->group(function () {
        Route::post('posts/{postId}/vote', [VoteController::class, 'vote']);
        Route::patch('posts/{postId}/upvote', [VoteController::class, 'upvote']);
        Route::patch('posts/{postId}/downvote', [VoteController::class, 'downvote']);
    });
});