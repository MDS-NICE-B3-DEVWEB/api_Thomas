<?php

use Illuminate\Support\Facades\Route; // Add this line
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BeatController;
use App\Http\Controllers\Api\SongController; 

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::get('posts', [PostController::class, 'index']);

Route::post('/register', [UserController::class, 'register']);
Route::get('/login', [UserController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    Route::get('/user', function () {
        $user = auth()->user();
        return $user;
    });

    // Beats routes
    Route::prefix('beats')->group(function () {
        Route::get('/', [BeatController::class, 'index']);
        Route::get('/{beat}', [BeatController::class, 'show']);
        Route::middleware('role:beatmaker')->group(function () {
            Route::post('/new', [BeatController::class, 'store']);
            Route::put('/{beat}', [BeatController::class, 'update']);
            Route::delete('/{beat}', [BeatController::class, 'destroy']);
        });
    });
    Route::get('/beatmakers/{beatmaker}/beats', [BeatController::class, 'beatmakerBeats']);

    // Songs routes
    Route::prefix('songs')->group(function () {
        Route::get('/', [SongController::class, 'index']);
        Route::get('/{song}', [SongController::class, 'show']);
        Route::middleware('role:artist')->group(function () {
            Route::post('/new', [SongController::class, 'store']);
            Route::put('/{song}', [SongController::class, 'update']);
            Route::delete('/{song}', [SongController::class, 'destroy']);
        });
    });
    Route::get('/artists/{artist}/songs', [SongController::class, 'artistSongs']);
});


